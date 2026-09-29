<?php

namespace App\Services\Transfer;

use App\Models\BatchTransfer;
use App\Models\TransactionChannel;
use App\Models\TransactionFee;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransferService
{
    public const FEE_MODULE = 'Transfer';

    // Used only when no row exists in transaction_fees
    public const FALLBACK_MIN_TRANSFER = 1.00;

    public function __construct(protected PaymongoTransferService $paymongo) {}

    /* ---------- Dynamic config ---------- */

    public function getFeeConfig(): ?TransactionFee
    {
        return TransactionFee::forModule(self::FEE_MODULE);
    }

    public function getMinTransfer(): float
    {
        $min = (float) ($this->getFeeConfig()?->minimum_fee ?? 0);

        return $min > 0 ? $min : self::FALLBACK_MIN_TRANSFER;
    }

    public function calculateFee(float $amount): float
    {
        return $this->getFeeConfig()?->calculate($amount) ?? 0.00;
    }

    public function getActiveChannels(): Collection
    {
        return TransactionChannel::active()->orderBy('id')->get();
    }

    public function findActiveChannel(string $code): ?TransactionChannel
    {
        return TransactionChannel::active()->where('code', $code)->first();
    }

    /* ---------- Transfer ---------- */

    public function transfer(User $user, array $data): BatchTransfer
    {
        $channelId = $data['channel_id'];
        $channel = $this->findActiveChannel($channelId);

        if (! $channel) {
            throw new DomainException('Unsupported or inactive destination channel.');
        }

        $amount = round((float) $data['amount'], 2);
        $minTransfer = $this->getMinTransfer();

        if ($amount < $minTransfer) {
            throw new DomainException('The minimum transfer amount is ₱'.number_format($minTransfer, 2));
        }

        $fee = $this->calculateFee($amount);
        $totalDeduct = round($amount + $fee, 2);
        $bic = $data['destination_bic'] ?? null;

        if (! $bic) {
            $bic = $this->paymongo->resolveBic($channel->search ?? $channel->name);
        }

        if (! $bic) {
            throw new DomainException("Could not resolve routing details for {$channel->name}. Please try again later.");
        }

        $referenceNumber = 'WD-'.Str::upper(Str::random(10));
        $sourceAccount = config('paymongo.source_account');
        $provider = $channel->provider ?: 'instapay';

        // 1. Lock Wallet, verify integrity, and Deduct Funds
        $batchTransfer = DB::transaction(function () use ($user, $data, $channel, $channelId, $bic, $amount, $fee, $totalDeduct, $sourceAccount, $referenceNumber, $provider) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new DomainException('Wallet not found.');
            }

            // Re-check under the lock: the controller's check happened
            // before this row was locked, so re-verify to close the gap.
            if ($wallet->isTampered()) {
                throw new DomainException('Your wallet balance integrity check failed. Transactions are restricted.');
            }

            if ((float) $wallet->balance < $totalDeduct) {
                throw new DomainException('Insufficient wallet balance.');
            }

            // decrement() bypasses Eloquent's saving event, so the signature
            // would go stale immediately after a legitimate debit. Use
            // assignment + save() instead so it recalculates correctly.
            $wallet->balance = (float) $wallet->balance - $totalDeduct;
            $wallet->save();

            $transfer = BatchTransfer::create([
                'wallet_id' => $wallet->id,
                'source_account_number' => $sourceAccount['number'] ?? null,
                'destination_account_number' => $data['account_number'],
                'destination_account_name' => $data['account_name'],
                'destination_account_bic' => $bic,
                'channel' => $channelId,
                'amount' => $amount,
                'fee' => $fee,
                'provider' => $provider,
                'purpose' => $data['purpose'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'reference_number' => $referenceNumber,
                'status' => 'pending',
            ]);

            $transfer->walletTransaction()->create([
                'wallet_id' => $wallet->id,
                'reference_number' => $referenceNumber,
                'type' => 'debit',
                'amount' => $amount,               // transfer amount only
                'transfer_fee' => $fee,            // fee charged
                'from_name' => $user->name,
                'to_account_name' => $data['account_name'],
                'to_account_number' => $data['account_number'],
                'to_provider' => $channel->name,   // use $provider for the raw rail
            ]);

            return $transfer;
        });

        $senderName = $user->name;
        $description = $data['remarks'] ?? "FISMPC Transfer from {$senderName} via {$channel->name}";

        $payload = [
            'provider' => $provider,
            'amount' => (int) round($amount * 100),
            'currency' => 'PHP',
            'purpose' => $data['purpose'] ?? 'Disbursement',
            'description' => Str::limit($description, 255),
            'reference_number' => $referenceNumber,
            'source_account' => [
                'number' => $sourceAccount['number'] ?? '',
                'name' => $sourceAccount['name'] ?? '',
                'bic' => $sourceAccount['bic'] ?? 'PAEYPHM2XXX',
            ],
            'destination_account' => [
                'number' => $data['account_number'],
                'name' => $data['account_name'],
                'bic' => $bic,
            ],
        ];

        if ($callbackUrl = config('paymongo.callback_url')) {
            $payload['callback_url'] = $callbackUrl;
        }

        try {
            $result = $this->paymongo->createBatchTransfer($payload);
            $transferData = $result['body']['data']['transfers'][0] ?? null;

            // Handled HTTP non-200 or payload error response from PayMongo
            if (! $result['ok'] || ! $transferData) {
                $errorMessage = $result['body']['errors'][0]['detail'] ?? 'Transfer could not be processed.';
                $failureCode = $result['body']['errors'][0]['code'] ?? 'api_error';

                $this->refundTransfer($batchTransfer, $failureCode, $result['body'] ?? []);

                throw new DomainException($errorMessage);
            }

            $batchTransfer->update([
                'paymongo_batch_id' => $result['body']['data']['id'] ?? null,
                'paymongo_transfer_id' => $transferData['id'] ?? null,
                'status' => $transferData['status'] ?? 'pending',
                'raw_response' => $result['body'] ?? [],
            ]);

            return $batchTransfer->fresh();

        } catch (Exception $e) {
            if ($e instanceof DomainException) {
                throw $e;
            }

            // Connection or timeout error: DO NOT refund immediately to avoid double spend if processed later.
            Log::error('Transfer API network error', [
                'reference' => $referenceNumber,
                'exception' => $e->getMessage(),
            ]);

            $batchTransfer->update([
                'status' => 'processing',
                'raw_response' => ['system_error' => $e->getMessage()],
            ]);

            throw new DomainException('Transfer request sent but confirmation is delayed. Check your transaction history shortly.');
        }
    }

    public function refundTransfer(BatchTransfer $transfer, string $failureCode, array $rawResponse = []): void
    {
        DB::transaction(function () use ($transfer, $failureCode, $rawResponse) {
            $transfer = BatchTransfer::whereKey($transfer->id)->lockForUpdate()->first();

            if (in_array($transfer->status, ['failed', 'returned', 'refunded'], true)) {
                return;
            }

            $wallet = Wallet::whereKey($transfer->wallet_id)->lockForUpdate()->first();

            if ($wallet) {
                if ($wallet->isTampered()) {
                    Log::warning('Refund blocked: wallet failed integrity check.', [
                        'wallet_id' => $wallet->id,
                        'transfer_reference' => $transfer->reference_number,
                    ]);
                } else {
                    $refundAmount = round((float) $transfer->amount + (float) $transfer->fee, 2);

                    $wallet->balance = (float) $wallet->balance + $refundAmount;
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
                        'description' => "Refund: Failed transfer ({$transfer->reference_number})",
                    ]);
                }
            }

            $transfer->update([
                'status' => 'failed',
                'failure_code' => $failureCode,
                'raw_response' => array_merge($transfer->raw_response ?? [], $rawResponse),
            ]);
        });
    }
}
