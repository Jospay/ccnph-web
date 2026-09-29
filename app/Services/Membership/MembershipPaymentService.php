<?php

namespace App\Services\Membership;

use App\Exceptions\Membership\MembershipGatewayException;
use App\Exceptions\Membership\MembershipPendingPaymentExistsException;
use App\Exceptions\Membership\MembershipScheduleAlreadyPaidException;
use App\Models\MembershipSchedule;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\TransactionFee;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipPaymentService
{
    public const FEE_MODULE = 'Membership';

    public function initiate(MembershipSchedule $schedule, int $paymentMethodId): array
    {
        return DB::transaction(function () use ($schedule, $paymentMethodId) {

            $schedule = MembershipSchedule::where('id', $schedule->id)
                ->lockForUpdate()
                ->with('membership')
                ->firstOrFail();

            // Clean old attempts
            $schedule->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            $this->validateSchedule($schedule);

            $method = PaymentMethod::findOrFail($paymentMethodId);
            $gateway = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gateway);

            $gatewayMethodId = $this->resolveGatewayMethodId($service, $method, []);

            // schedule->amount is in cents — net amount owed for this installment
            $amountPesos = round($schedule->amount / 100, 2);

            $fee = TransactionFee::forModule(self::FEE_MODULE)?->calculate($amountPesos) ?? 0.00;
            $feeCents = (int) round($fee * 100);
            $totalChargeCents = (int) round($schedule->amount + $feeCents);

            // Charge amount + fee via gateway
            $intentResponse = $service->createPaymentIntent($totalChargeCents / 100);

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw MembershipGatewayException::failedToCreatePaymentIntent(
                    data_get($intentResponse, 'errors')
                );

            $attached = $service->attach($intentId, $gatewayMethodId);

            $payment = $schedule->payments()->create([
                'payment_method_id' => $paymentMethodId,
                'status_id' => Status::PENDING,
                'payment_date' => now(),
                'amount' => $schedule->amount,
                'fee' => $feeCents,
                'gateway' => $gateway,
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
                'gateway_status' => data_get($attached, 'data.attributes.status'),
                'idempotency_key' => Str::uuid(),
            ]);

            return [
                'payment' => $payment,
                'next_action' => $service->getNextAction($attached),
            ];
        });
    }

    private function validateSchedule(MembershipSchedule $schedule): void
    {
        if ($schedule->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a cancelled schedule.');
        }

        if ($schedule->membership->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a schedule for a cancelled membership.');
        }

        if (! in_array($schedule->membership->status_id, [Status::ACTIVE, Status::APPROVED])) {
            throw new \RuntimeException('Membership is not in a payable state.');
        }

        if ($schedule->status_id === Status::PAID) {
            throw new MembershipScheduleAlreadyPaidException($schedule);
        }

        if ($schedule->payments()->where('status_id', Status::PENDING)->exists()) {
            throw new MembershipPendingPaymentExistsException($schedule);
        }
    }

    private function resolveGatewayMethodId($gateway, PaymentMethod $method, array $data): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw MembershipGatewayException::missingClientSideMethodId();
        }

        $response = $gateway->createPaymentMethod($method->gateway_type);

        return data_get($response, 'data.id')
            ?? throw MembershipGatewayException::failedToCreatePaymentMethod(
                data_get($response, 'errors')
            );
    }
}
