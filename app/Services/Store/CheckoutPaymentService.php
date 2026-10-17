<?php

namespace App\Services\Store;

use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Point;
use App\Models\Status;
use App\Models\User;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Points\PointService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CheckoutPaymentService
{
    public function __construct(protected PointService $pointService) {}

    /**
     * Create a PayMongo intent for a pending checkout.
     * Returns payment + next_action (QR url or redirect url).
     */
    public function createOnlinePayment(User $user, Checkout $checkout, PaymentMethod $method): array
    {
        $payment = Payment::where('payable_type', Checkout::class)
            ->where('payable_id', $checkout->id)
            ->firstOrFail();

        try {
            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $senderName = $this->senderName($user);

            $pm = $service->createPaymentMethod($method->gateway_type, [
                'billing' => [
                    'name' => $senderName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]);

            $gatewayMethodId = data_get($pm, 'data.id')
                ?? throw new DomainException('Failed to create payment method.');

            $intentResponse = $service->createPaymentIntent(
                $payment->amount / 100,
                [
                    'description' => Str::limit("CCNPH Store Order {$checkout->checkout_number} - {$senderName}", 255),
                    'statement_descriptor' => 'FCCNPH',
                    'statement_descriptor_suffix' => 'STORE',
                ]
            );

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw new DomainException('Failed to create payment intent.');

            $attached = $service->attach($intentId, $gatewayMethodId);

            $payment->update([
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
                'sender_name' => $senderName.' ('.$method->name.')',
                'sender_account_number' => data_get($attached, 'data.attributes.billing.phone'),
            ]);

            return [
                'payment' => $payment->fresh(),
                'next_action' => $service->getNextAction($attached),
            ];
        } catch (Throwable $e) {
            $payment->update(['status_id' => Status::FAILED]);

            throw $e instanceof DomainException
                ? $e
                : new DomainException('Unable to start the online payment. Please try again.');
        }
    }

    /**
     * Pay a checkout using the user's points balance.
     * Must run inside the same DB transaction that created the checkout.
     */
    public function payWithPoints(User $user, Checkout $checkout): void
    {
        $point = $this->pointService->getUserPoint($user);
        $point = Point::whereKey($point->id)->lockForUpdate()->firstOrFail();

        if ($point->isTampered()) {
            throw new DomainException('Your points balance integrity check failed. Transactions are restricted.');
        }

        $total = round((float) $checkout->grand_total, 2);

        if ((float) $point->balance < $total) {
            throw new DomainException('Insufficient points balance.');
        }

        $point->withdraw(
            $total,
            $checkout,
            'Store purchase '.$checkout->checkout_number,
            0.00,
            $checkout->checkout_number,
            'Store Checkout',
        );
    }

    private function senderName(User $user): string
    {
        return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User');
    }
}
