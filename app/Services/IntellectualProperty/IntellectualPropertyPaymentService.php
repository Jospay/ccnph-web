<?php

namespace App\Services\IntellectualProperty;

use App\Exceptions\IntellectualProperty\IntellectualPropertyAlreadyAppliedPaymentSettingsException;
use App\Exceptions\IntellectualProperty\IntellectualPropertyAlreadyPaidException;
use App\Exceptions\IntellectualProperty\IntellectualPropertyGatewayException;
use App\Exceptions\IntellectualProperty\IntellectualPropertyInvalidTermException;
use App\Exceptions\IntellectualProperty\IntellectualPropertyNotReadyForPaymentException;
use App\Exceptions\IntellectualProperty\IntellectualPropertyPaymentExistsException;
use App\Models\IntellectualProperty;
use App\Models\IntellectualPropertySchedule;
use App\Models\IntellectualPropertySetting;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\User;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntellectualPropertyPaymentService
{
    public function initiate(
        IntellectualPropertySchedule $schedule,
        int $paymentMethodId,
        string $gateway = 'paymongo'
    ): array {

        return DB::transaction(function () use ($schedule, $paymentMethodId, $gateway) {

            $schedule = IntellectualPropertySchedule::where('id', $schedule->id)
                ->lockForUpdate()
                ->with('intellectualProperty')
                ->firstOrFail();

            // Clean old attempts
            $schedule->payments()
                ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
                ->update(['status_id' => Status::ARCHIVED]);

            $this->validateSchedule($schedule);

            $method = PaymentMethod::findOrFail($paymentMethodId);

            /**
             * ======================================
             * 🟢 WALLET FLOW (NEW)
             * ======================================
             */
            if ($gateway === 'wallet' || $method->gateway_type === 'wallet') {

                $wallet = $schedule->intellectualProperty->user
                    ->wallet()
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = $schedule->amount / 100;

                if ($wallet->balance < $amount) {
                    throw new \DomainException('Insufficient wallet balance.');
                }

                $wallet->decrement('balance', $amount);

                $payment = $schedule->payments()->create([
                    'payment_method_id' => $paymentMethodId,
                    'status_id' => Status::SUCCESS,
                    'payment_date' => now(),
                    'amount' => $schedule->amount,
                    'gateway' => 'wallet',
                ]);

                $schedule->onPaymentSuccess($payment);

                return [
                    'payment' => $payment,
                    'next_action' => null,
                ];
            }

            /**
             * ======================================
             * 🟡 EXISTING PAYMONGO FLOW (UNCHANGED)
             * ======================================
             */

            $gatewayService = PaymentGatewayFactory::resolveGateway($method);
            $service = PaymentGatewayFactory::make($gatewayService);

            $gatewayMethodId = $this->resolveGatewayMethodId($service, $method, []);

            $intentResponse = $service->createPaymentIntent($schedule->amount / 100);

            $intentId = data_get($intentResponse, 'data.id')
                ?? throw IntellectualPropertyGatewayException::failedToCreatePaymentIntent(
                    data_get($intentResponse, 'errors')
                );

            $attached = $service->attach($intentId, $gatewayMethodId);

            $payment = $schedule->payments()->create([
                'payment_method_id' => $paymentMethodId,
                'status_id' => Status::PENDING,
                'payment_date' => now(),
                'amount' => $schedule->amount,
                'gateway' => $gatewayService,
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

    // =========================
    // EXISTING METHODS (UNCHANGED)
    // =========================

    public function applyPayment(IntellectualProperty $ip, User $user, int $termMonths): IntellectualProperty
    {
        $this->ensureNoExistingSchedules($ip);
        $this->ensureReadyForPayment($ip);

        $setting = IntellectualPropertySetting::current($ip);

        if (!in_array($termMonths, $setting->allowed_term_months)) {
            throw new IntellectualPropertyInvalidTermException($termMonths, $setting->allowed_term_months);
        }

        return DB::transaction(function () use ($ip, $user, $termMonths, $setting) {
            $ip->update([
                'amount' => $setting->amount,
                'term_months' => $termMonths,
            ]);

            $this->generateSchedules($ip);

            return $ip;
        });
    }

    public function ensureReadyForPayment(IntellectualProperty $ip): void
    {
        if (!in_array($ip->status_id, [Status::WAITING_FOR_PAYMENT])) {
            throw new IntellectualPropertyNotReadyForPaymentException($ip->status_id);
        }
    }

    private function ensureNoExistingSchedules(IntellectualProperty $ip): void
    {
        if ($ip->schedules()->exists()) {
            throw new IntellectualPropertyAlreadyAppliedPaymentSettingsException();
        }
    }

    private function generateSchedules(IntellectualProperty $ip): void
    {
        $total = $ip->amount;
        $months = $ip->term_months;
        $installment = (int) floor($total / $months);
        $remainder = $total - ($installment * $months);

        $schedules = [];

        for ($i = 1; $i <= $months; $i++) {
            $schedules[] = [
                'intellectual_property_id' => $ip->id,
                'status_id' => Status::UNPAID,
                'installment_no' => $i,
                'amount' => $i === $months
                    ? $installment + $remainder
                    : $installment,
                'due_date' => now()->addMonths($i)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        IntellectualPropertySchedule::insert($schedules);
    }

    private function validateSchedule(IntellectualPropertySchedule $schedule): void
    {
        if ($schedule->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a cancelled schedule.');
        }

        if ($schedule->intellectualProperty->status_id === Status::CANCELLED) {
            throw new \RuntimeException('Cannot pay a schedule for a cancelled Intellectual Property.');
        }

        if (!in_array($schedule->intellectualProperty->status_id, [Status::WAITING_FOR_PAYMENT])) {
            throw new \RuntimeException('Membership is not in a payable state.');
        }

        if ($schedule->status_id === Status::PAID) {
            throw new IntellectualPropertyAlreadyPaidException($schedule);
        }

        if ($schedule->payments()
            ->where('status_id', Status::PENDING)
            ->where('created_at', '>', now()->subMinutes(1))
            ->exists()) {
            throw new IntellectualPropertyPaymentExistsException($schedule);
        }
    }

    private function resolveGatewayMethodId($gateway, PaymentMethod $method, array $data): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw IntellectualPropertyGatewayException::missingClientSideMethodId();
        }

        $response = $gateway->createPaymentMethod($method->gateway_type);

        return data_get($response, 'data.id')
            ?? throw IntellectualPropertyGatewayException::failedToCreatePaymentMethod(
                data_get($response, 'errors')
            );
    }
}
