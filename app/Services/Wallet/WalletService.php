<?php

namespace App\Services\Wallet;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payments\PaymentGatewayFactory;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public const PRESET_AMOUNTS = [10000, 20000, 50000, 100000, 200000, 500000];

    /**
     * Get or create the user's wallet.
     */
    public function getUserWallet(User $user): Wallet
    {
        // Ensures a wallet exists for the user (firstOrCreate pattern)
        return $user->wallet ?: $user->wallet()->create(['balance' => 0]);
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
        $amount = $data['amount']; // in cents

        return DB::transaction(function () use ($wallet, $data, $amount) {
            // Clean old failed/cancelled attempts
            $wallet->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            // Block in-flight payment
            if ($wallet->payments()->where('status_id', Status::PENDING)->exists()) {
                throw new DomainException('A pending recharge already exists.');
            }

            $method = PaymentMethod::findOrFail($data['payment_method_id']);
            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $gatewayMethodId = $this->resolveGatewayMethodId($service, $method, $data);

            $intentResponse = $service->createPaymentIntent($amount / 100);

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw new DomainException('Failed to create payment intent.');

            $attached = $service->attach($intentId, $gatewayMethodId);

            $payment = $wallet->payments()->create([
                'payment_method_id' => $data['payment_method_id'],
                'status_id' => Status::PENDING,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        });
    }

    private function resolveGatewayMethodId($service, PaymentMethod $method, array $data): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw new DomainException('Missing gateway_payment_method_id for client-side method.');
        }

        $response = $service->createPaymentMethod($method->gateway_type);

        return data_get($response, 'data.id')
            ?? throw new DomainException('Failed to create payment method.');
    }
}
