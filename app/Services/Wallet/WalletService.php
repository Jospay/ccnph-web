<?php

namespace App\Services\Wallet;

use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\TransactionFee;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payments\PaymentGatewayFactory;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public const PRESET_AMOUNTS = [10000, 20000, 50000, 100000, 200000, 500000];

    public const FEE_MODULE = 'Load';

    public const LOAD_LABEL = 'Load Wallet';

    // Used only when no row exists in transaction_fees for module "Load"
    public const FALLBACK_MIN_RECHARGE = 100.00;

    /* ---------- Dynamic config ---------- */

    public function getFeeConfig(): ?TransactionFee
    {
        return TransactionFee::forModule(self::FEE_MODULE);
    }

    public function getMinRecharge(): float
    {
        $min = (float) ($this->getFeeConfig()?->minimum_fee ?? 0);

        return $min > 0 ? $min : self::FALLBACK_MIN_RECHARGE;
    }

    public function calculateFee(float $amount): float
    {
        return $this->getFeeConfig()?->calculate($amount) ?? 0.00;
    }

    /**
     * Get or create the user's wallet.
     */
    public function getUserWallet(User $user): Wallet
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0.00,
                'show' => true,
            ]
        );

        if ($wallet->wasRecentlyCreated || is_null($wallet->signature)) {
            $wallet->signature = $wallet->generateSignature();
            $wallet->save();
        }

        return $wallet;
    }

    /**
     * Get paginated transactions.
     */
    public function getWalletTransactions(Wallet $wallet): LengthAwarePaginator
    {
        return $wallet->walletTransactions()
            ->with('reference')
            ->latest()
            ->paginate(15);
    }

    public function recharge(User $user, array $data): array
    {
        $wallet = $this->getUserWallet($user);

        $amount = (float) $data['amount']; // cents — net amount to be credited to the wallet
        $amountPesos = round($amount / 100, 2);

        $minRecharge = $this->getMinRecharge();

        if ($amountPesos < $minRecharge) {
            throw new DomainException('The minimum load amount is ₱'.number_format($minRecharge, 2));
        }

        $fee = $this->calculateFee($amountPesos); // pesos
        $feeCents = (int) round($fee * 100);
        $totalChargeCents = (int) round($amount + $feeCents);

        return DB::transaction(function () use ($wallet, $user, $data, $amount, $feeCents, $totalChargeCents) {
            // Clean old failed/cancelled attempts
            $wallet->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            // Allow only one recharge request per minute
            $lastRecharge = $wallet->payments()
                ->latest('created_at')
                ->first();

            if ($lastRecharge && $lastRecharge->created_at->gt(now()->subMinute())) {
                throw new DomainException(
                    'Please wait 1 minute before requesting another recharge.'
                );
            }

            $method = PaymentMethod::findOrFail($data['payment_method_id']);

            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $gatewayMethodId = $this->resolveGatewayMethodId(
                $service,
                $method,
                $data,
                $user,
            );

            // Fetch full sender name cleanly across name / first_name + last_name
            $senderName = trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
                ?: ($user->name ?? 'User');

            // Force full description with sender name
            $description = Str::limit("FISMPC Load Wallet - {$senderName}", 255);

            // Charge amount + fee via gateway
            $intentResponse = $service->createPaymentIntent(
                $totalChargeCents / 100,
                [
                    'description' => $description,
                    'statement_descriptor' => 'FISMPC',
                    'statement_descriptor_suffix' => 'LOAD',
                ]
            );

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw new DomainException('Failed to create payment intent.');

            $attached = $service->attach(
                $intentId,
                $gatewayMethodId
            );

            [$senderName, $senderAccountNumber] = $this->resolveSenderDetails($attached, $user, $method, $data);

            $payment = $wallet->payments()->create([
                'payment_method_id' => $data['payment_method_id'],
                'status_id' => Status::PENDING,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'fee' => $feeCents,
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
                'sender_name' => $senderName,
                'sender_account_number' => $senderAccountNumber,
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        });
    }

    private function resolveSenderDetails(array $attached, User $user, PaymentMethod $method, array $data = []): array
    {
        $billingName = data_get($attached, 'data.attributes.billing.name');
        $billingPhone = data_get($attached, 'data.attributes.billing.phone');
        $last4 = data_get($attached, 'data.attributes.payment_method.details.last4');

        $fallbackName = trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
            ?: ($user->name ?? 'User');

        $declaredName = trim((string) ($data['sender_account_name'] ?? ''));

        $resolvedName = $declaredName !== ''
            ? $declaredName
            : ($billingName ?: $fallbackName);

        $senderName = $resolvedName.' ('.$method->name.')';
        $senderAccountNumber = $last4 ? "**** {$last4}" : $billingPhone;

        return [$senderName, $senderAccountNumber];
    }

    private function resolveGatewayMethodId($service, PaymentMethod $method, array $data, User $user): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw new DomainException('Missing gateway_payment_method_id for client-side method.');
        }

        $senderName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name;

        $response = $service->createPaymentMethod($method->gateway_type, [
            'billing' => [
                'name' => $senderName,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);

        return data_get($response, 'data.id')
            ?? throw new DomainException('Failed to create payment method.');
    }
}
