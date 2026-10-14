<?php

namespace App\Services\Points;

use App\Models\PaymentMethod;
use App\Models\Point;
use App\Models\Status;
use App\Models\TransactionFee;
use App\Models\User;
use App\Services\Payments\PaymentGatewayFactory;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PointService
{
    public const FEE_MODULE = 'Point Load';

    // Used only when no row exists in transaction_fees for module "Point Load"
    public const FALLBACK_MIN_LOAD = 100.00;

    // Only guards against double-submits, not legit retries after cancelling a QR
    public const DUPLICATE_REQUEST_SECONDS = 15;

    /* ---------- Dynamic config ---------- */

    public function getFeeConfig(): ?TransactionFee
    {
        return TransactionFee::forModule(self::FEE_MODULE);
    }

    public function getMinLoad(): float
    {
        $min = (float) ($this->getFeeConfig()?->minimum_fee ?? 0);

        return $min > 0 ? $min : self::FALLBACK_MIN_LOAD;
    }

    public function calculateFee(float $amount): float
    {
        return $this->getFeeConfig()?->calculate($amount) ?? 0.00;
    }

    /**
     * Get or create the user's point account.
     */
    public function getUserPoint(User $user): Point
    {
        $point = Point::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0.00,
                'show' => true,
            ]
        );

        if ($point->wasRecentlyCreated || is_null($point->signature)) {
            $point->signature = $point->generateSignature();
            $point->save();
        }

        return $point;
    }

    public function getPointTransactions(Point $point): LengthAwarePaginator
    {
        return $point->pointTransactions()
            ->with('reference')
            ->latest()
            ->paginate(15);
    }

    public function load(User $user, array $data): array
    {
        $point = $this->getUserPoint($user);

        $amount = (float) $data['amount']; // cents — net amount credited as points
        $amountPesos = round($amount / 100, 2);

        $min = $this->getMinLoad();

        if ($amountPesos < $min) {
            throw new DomainException('The minimum load amount is ₱'.number_format($min, 2));
        }

        $fee = $this->calculateFee($amountPesos);
        $feeCents = (int) round($fee * 100);
        $totalChargeCents = (int) round($amount + $feeCents);

        return DB::transaction(function () use ($point, $user, $data, $amount, $feeCents, $totalChargeCents) {
            // Clean old failed/cancelled attempts
            $point->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            // Block only rapid duplicate requests (double tap), not retries after a
            // cancelled QR or a completed load.
            $hasRecentPending = $point->payments()
                ->where('status_id', Status::PENDING)
                ->where('created_at', '>', now()->subSeconds(self::DUPLICATE_REQUEST_SECONDS))
                ->exists();

            if ($hasRecentPending) {
                throw new DomainException('A load request is already in progress. Please wait a few seconds and try again.');
            }

            $method = PaymentMethod::findOrFail($data['payment_method_id']);

            if ($method->isOffline()) {
                throw new DomainException('This payment method is not available for loading points.');
            }

            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $gatewayMethodId = $this->resolveGatewayMethodId($service, $method, $data, $user);

            $senderName = $this->senderName($user);

            // Charge amount + fee via gateway
            $intentResponse = $service->createPaymentIntent(
                $totalChargeCents / 100,
                [
                    'description' => Str::limit("CCNPH Load Points - {$senderName}", 255),
                    'statement_descriptor' => 'FCCNPH',
                    'statement_descriptor_suffix' => 'POINTS',
                ]
            );

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw new DomainException('Failed to create payment intent.');

            $attached = $service->attach($intentId, $gatewayMethodId);

            $billingName = data_get($attached, 'data.attributes.billing.name');
            $billingPhone = data_get($attached, 'data.attributes.billing.phone');
            $last4 = data_get($attached, 'data.attributes.payment_method.details.last4');

            $payment = $point->payments()->create([
                'payment_method_id' => $data['payment_method_id'],
                'status_id' => Status::PENDING,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'fee' => $feeCents,
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
                'sender_name' => ($billingName ?: $senderName).' ('.$method->name.')',
                'sender_account_number' => $last4 ? "**** {$last4}" : $billingPhone,
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        });
    }

    private function senderName(User $user): string
    {
        return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User');
    }

    private function resolveGatewayMethodId($service, PaymentMethod $method, array $data, User $user): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw new DomainException('Missing gateway_payment_method_id for client-side method.');
        }

        $response = $service->createPaymentMethod($method->gateway_type, [
            'billing' => [
                'name' => $this->senderName($user),
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);

        return data_get($response, 'data.id')
            ?? throw new DomainException('Failed to create payment method.');
    }
}
