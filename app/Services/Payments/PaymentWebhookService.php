<?php

namespace App\Services\Payments;

use App\Contracts\Payable;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\Status;
use App\Services\Cooperative\CooperativeRevenueAllocatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    public function __construct(
        private readonly CooperativeRevenueAllocatorService $revenueAllocator
    ) {}

    /**
     * Create a new class instance.
     */
    public function handle(
        string $gatewayPaymentIntentId,
        string $gatewayStatus,
        ?string $gatewayPaymentId = null
    ): void {
        $status = $this->normalizeStatus($gatewayStatus);
        $payment = Payment::where('gateway_payment_intent_id', $gatewayPaymentIntentId)->first();

        if (! $payment) {
            Log::warning('Webhook received for unknown payment intent.', [
                'intent_id' => $gatewayPaymentIntentId,
            ]);
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => 'paymongo',
                'event' => 'unknown_payment_intent',
                'payload' => [
                    'intent_id' => $gatewayPaymentIntentId,
                    'status' => $status,
                ],
            ]);

            return;
        }

        // Idempotency guard
        if ((int) $payment->status_id === Status::SUCCESS) {
            Log::info('Webhook already processed.', ['payment_id' => $payment->id]);
            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'already_processed',
                'payload' => [],
            ]);

            return;
        }

        $payable = $payment->payable;

        // check the contract
        if (! $payable instanceof Payable) {
            Log::warning('Payable does not implement Payable contract.', [
                'payment_id' => $payment->id,
                'payable_type' => $payment->payable_type,
            ]);
            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'invalid_payable',
                'payload' => ['type' => $payment->payable_type],
            ]);

            return;
        }

        if ($status === 'paid') {
            DB::transaction(function () use ($payment, $payable, $gatewayPaymentId) {
                $payment->update([
                    'status_id' => Status::SUCCESS,
                    'gateway_payment_id' => $gatewayPaymentId,
                ]);

                // delegates to Payable model
                $payable->onPaymentSuccess($payment);

                PaymentGatewayLog::create([
                    'payment_id' => $payment->id,
                    'gateway' => $payment->gateway ?? 'paymongo',
                    'event' => 'payment_success',
                    'payload' => [
                        'intent_id' => $payment->gateway_payment_intent_id,
                        'gateway_payment_id' => $gatewayPaymentId,
                        'payable_type' => $payment->payable_type,
                        'payable_id' => $payment->payable_id,
                    ],
                ]);
            });

            // After commit: allocation problems must never undo a paid payment
            $this->allocateRevenue($payment, $payable);
        }

        if ($status === 'failed') {
            DB::transaction(function () use ($payment, $payable, $gatewayPaymentIntentId) {
                $payment->update(['status_id' => Status::FAILED]);

                $payable->onPaymentFailed($payment);

                PaymentGatewayLog::create([
                    'payment_id' => $payment->id,
                    'gateway' => $payment->gateway ?? 'paymongo',
                    'event' => 'payment_failed',
                    'payload' => [
                        'intent_id' => $gatewayPaymentIntentId,
                        'payable_type' => $payment->payable_type,
                        'payable_id' => $payment->payable_id,
                    ],
                ]);
            });

            Log::info('Payment marked as failed.', ['payment_id' => $payment->id]);
        }
    }

    private function allocateRevenue(Payment $payment, Payable $payable): void
    {
        $slug = null;

        try {
            $slug = $payable->cooperativeServiceSlug();

            if (! $slug) {
                return;
            }

            $this->revenueAllocator->allocate(
                serviceSlug: $slug,
                amount: $payment->amount / 100,
                cooperativeId: $payable->cooperativeId(),
            );
        } catch (\Throwable $e) {
            report($e);

            Log::error('Revenue allocation failed after successful payment.', [
                'payment_id' => $payment->id,
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => $payment->gateway ?? 'paymongo',
                'event' => 'allocation_failed',
                'payload' => ['slug' => $slug, 'error' => $e->getMessage()],
            ]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'paid', 'payment.paid', 'success', 'succeeded' => 'paid',
            'failed', 'payment.failed' => 'failed',
            default => $status,
        };
    }
}
