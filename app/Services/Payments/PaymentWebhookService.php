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
     * Handle incoming payment status webhook event.
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

        // Verify Payable contract implementation
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

                // Delegate completion actions to payable model
                $payable->onPaymentSuccess($payment);

                // Dynamically extract cooperative_id from payable relations
                $cooperativeId = $this->resolveCooperativeId($payable);

                // Record transaction share into revenue breakdowns
                if ($slug = $payable->cooperativeServiceSlug()) {
                    try {
                        $this->revenueAllocator->allocate(
                            serviceSlug: $slug,
                            amount: $payment->amount / 100,
                            cooperativeId: $cooperativeId,
                        );
                    } catch (\Throwable $e) {
                        Log::error('Revenue allocation failed.', [
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

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

    /**
     * Dynamically resolve cooperative_id from the polymorphic model structure.
     */
    private function resolveCooperativeId(mixed $payable): ?int
    {
        if (! is_object($payable)) {
            return null;
        }

        // 1. Check direct user relation (e.g. MemberShareCapital, Wallet)
        if (method_exists($payable, 'user')) {
            $payable->loadMissing('user');
            if ($payable->user?->cooperative_id !== null) {
                return (int) $payable->user->cooperative_id;
            }
        }

        // 2. Check nested user relations for schedule models
        if (method_exists($payable, 'loan')) {
            $payable->loadMissing('loan.user');
            if ($payable->loan?->user?->cooperative_id !== null) {
                return (int) $payable->loan->user->cooperative_id;
            }
        }

        if (method_exists($payable, 'intellectualProperty')) {
            $payable->loadMissing('intellectualProperty.user');
            if ($payable->intellectualProperty?->user?->cooperative_id !== null) {
                return (int) $payable->intellectualProperty->user->cooperative_id;
            }
        }

        if (method_exists($payable, 'membership')) {
            $payable->loadMissing('membership.user');
            if ($payable->membership?->user?->cooperative_id !== null) {
                return (int) $payable->membership->user->cooperative_id;
            }
        }

        if (method_exists($payable, 'shareCapital')) {
            $payable->loadMissing('shareCapital.user');
            if ($payable->shareCapital?->user?->cooperative_id !== null) {
                return (int) $payable->shareCapital->user->cooperative_id;
            }
        }

        return null;
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
