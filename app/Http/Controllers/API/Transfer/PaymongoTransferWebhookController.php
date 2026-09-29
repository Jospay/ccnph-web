<?php

namespace App\Http\Controllers\API\Transfer;

use App\Http\Controllers\Controller;
use App\Models\BatchTransfer;
use App\Models\TransactionChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymongoTransferWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = config('paymongo.webhook_secret');
        $signatureHeader = $request->header('Paymongo-Signature');

        if (! $secret || ! $signatureHeader || ! $this->isValidSignature($request->getContent(), $signatureHeader, $secret)) {
            Log::warning('Rejected PayMongo transfer webhook: invalid signature.');

            return response('Invalid signature', 400);
        }

        $payload = $request->json()->all();
        Log::info('PayMongo transfer webhook received', $payload);

        // Fallback-friendly payload parsing (covers both nested event data & direct object payloads)
        $eventData = $payload['data']['attributes']['data'] ?? $payload['data'] ?? [];
        $transferId = $eventData['id'] ?? null;
        $status = $eventData['attributes']['status'] ?? $eventData['status'] ?? null;

        if (! $transferId || ! $status) {
            return response('Ignored - Missing required attributes', 200);
        }

        $batchTransfer = BatchTransfer::where('paymongo_transfer_id', $transferId)->first();

        if (! $batchTransfer) {
            return response('No matching transfer record', 200);
        }

        DB::transaction(function () use ($batchTransfer, $status, $payload) {
            // Lock transfer record for update to prevent race conditions
            $transfer = BatchTransfer::whereKey($batchTransfer->id)
                ->lockForUpdate()
                ->first();

            // Idempotency check: Ignore if status is unchanged
            if (! $transfer || $transfer->status === $status) {
                return;
            }

            $isFailure = in_array($status, ['failed', 'returned'], true);
            $alreadyRefunded = in_array($transfer->status, ['failed', 'returned', 'refunded'], true);

            $transfer->update([
                'status' => $status,
                'raw_response' => array_merge($transfer->raw_response ?? [], ['webhook' => $payload]),
            ]);

            // Refund wallet if transaction failed and hasn't been refunded yet
            if ($isFailure && ! $alreadyRefunded) {
                $wallet = $transfer->wallet()->lockForUpdate()->first();

                if ($wallet) {
                    if ($wallet->isTampered()) {
                        Log::warning('Webhook refund blocked: wallet failed integrity check.', [
                            'wallet_id' => $wallet->id,
                            'transfer_reference' => $transfer->reference_number,
                        ]);
                    } else {
                        $refund = round((float) $transfer->amount + (float) $transfer->fee, 2);

                        $wallet->balance = (float) $wallet->balance + $refund;
                        $wallet->save();

                        $channelName = TransactionChannel::where('code', $transfer->channel)->value('name')
                            ?? $transfer->channel;

                        // "-RF" keeps the reference unique (wallet_transactions.reference_number is unique)
                        $transfer->walletTransaction()->create([
                            'wallet_id' => $wallet->id,
                            'reference_number' => $transfer->reference_number.'-RF',
                            'type' => 'credit',
                            'amount' => (float) $transfer->amount,
                            'transfer_fee' => (float) $transfer->fee,
                            'from_name' => $wallet->user?->name,
                            'to_account_name' => $transfer->destination_account_name,
                            'to_account_number' => $transfer->destination_account_number,
                            'to_provider' => $channelName,
                            'description' => "Refund for failed transfer {$transfer->reference_number}",
                        ]);
                    }
                }
            }
        });

        return response('OK', 200);
    }

    protected function isValidSignature(string $payload, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[trim((string) $key)] = $value !== null ? trim($value) : null;
        }

        if (empty($parts['t'])) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$parts['t']}.{$payload}", $secret);

        foreach (array_filter([$parts['li'] ?? null, $parts['te'] ?? null]) as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }
}
