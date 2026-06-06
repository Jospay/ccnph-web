<?php

namespace App\Services\ShareCapital;

use App\Exceptions\Loan\LoanGatewayException;
use App\Models\MemberShareCapital;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ShareCapitalSchedule;
use App\Models\ShareCapitalSetting;
use App\Models\Status;
use App\Models\User;
use App\Services\Payments\PaymentGatewayFactory;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ShareCapitalService
{
    public function apply(User $user, array $data): MemberShareCapital
    {
        if ($user->shareCapital) {
            throw new DomainException('You already have an existing share capital.');
        }

        $setting = ShareCapitalSetting::getLatest();

        if (!$setting) {
            throw new DomainException('Share capital is not configured yet.');
        }

        if (!in_array($data['term_months'], $setting->allowed_term_months)) {
            throw new DomainException('Invalid term selected.');
        }

        return DB::transaction(function () use ($user, $data, $setting) {
            $shareCapital = MemberShareCapital::create([
                'user_id' => $user->id,
                'status_id' => Status::PENDING,
                'amount' => $setting->required_amount,
                'term_months' => $data['term_months'],
            ]);

            $installmentAmount = intdiv($setting->required_amount, $data['term_months']);
            $remainder = $setting->required_amount - ($installmentAmount * $data['term_months']);

            for ($i = 1; $i <= $data['term_months']; $i++) {
                $amount = $i === $data['term_months']
                    ? $installmentAmount + $remainder
                    : $installmentAmount;

                ShareCapitalSchedule::create([
                    'member_share_capital_id' => $shareCapital->id,
                    'status_id' => Status::UNPAID,
                    'installment_no' => $i,
                    'amount' => $amount,
                    'due_date' => Carbon::now()->addMonths($i)->startOfMonth(),
                ]);
            }

            return $shareCapital;
        });
    }

  public function pay(
    User $user,
    ShareCapitalSchedule $schedule,
    array $data
): array
{
    $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);

    return DB::transaction(function () use ($user, $schedule, $data, $paymentMethod){

        $schedule = $this->lockAndValidateSchedule(
            $user,
            $schedule
        );
            $payment = $this->createPendingPayment($schedule, $data, $paymentMethod);

            if ($paymentMethod->isOffline()) {
                return $this->settleOffline($payment, $paymentMethod);
            }

            return $this->processGatewayPayment($payment, $paymentMethod, $data);
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function lockAndValidateSchedule(
    User $user,
    ShareCapitalSchedule $schedule
): ShareCapitalSchedule
{
    $schedule = ShareCapitalSchedule::query()
        ->where('id', $schedule->id)
        ->lockForUpdate()
        ->with('shareCapital')
        ->firstOrFail();

        if ($schedule->shareCapital->user_id !== $user->id) {
            throw new DomainException('Unauthorized.');
        }

        if ($schedule->status_id === Status::PAID) {
            throw new DomainException('This schedule is already paid.');
        }

        if ($this->hasPendingPayment($schedule)) {
            throw new DomainException('A pending payment already exists for this schedule.');
        }

        return $schedule;
    }

    private function validateAmount(ShareCapitalSchedule $schedule, int $providedInCents): void
    {
        $paidInCents = (int) $schedule->payments()
            ->where('status_id', Status::SUCCESS)
            ->sum('amount');

        $remainingInCents = $schedule->amount - $paidInCents;

        if ($remainingInCents <= 0) {
            throw new DomainException('This schedule is already fully paid.');
        }

        if ($providedInCents !== $remainingInCents) {
            throw new DomainException(
                'Amount must exactly match the remaining balance of '
                . number_format($remainingInCents / 100, 2)
            );
        }
    }

    private function createPendingPayment(
        ShareCapitalSchedule $schedule,
        array $data,
        PaymentMethod $paymentMethod
    ): Payment {
        $providedInCents = (int) round((float) $data['amount'] * 100);
        $this->validateAmount($schedule, $providedInCents);

        // Archive stale failed/cancelled attempts before creating a fresh one
        $schedule->payments()
            ->whereIn('status_id', [Status::FAILED, Status::CANCELLED])
            ->update(['status_id' => Status::ARCHIVED]);

        return $schedule->payments()->create([
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'amount' => $providedInCents,
            'gateway' => PaymentGatewayFactory::resolveGateway($paymentMethod),
            'status_id' => Status::PENDING,
        ]);
    }

    private function settleOffline(Payment $payment, PaymentMethod $paymentMethod): array
    {
        if ($paymentMethod->id === PaymentMethod::WALLET) {
            $this->deductFromWallet($payment);
        }

        $payment->update(['status_id' => Status::SUCCESS]);
        $payment->payable->onPaymentSuccess($payment);

        return ['payment' => $payment, 'next_action' => null];
    }

    private function deductFromWallet(Payment $payment): void
    {
        $shareCapital = $payment->payable->shareCapital;
        $wallet = $shareCapital->user->wallet()->lockForUpdate()->firstOrFail();
        $amountInPhp = $payment->amount / 100;

        if ($wallet->balance < $amountInPhp) {
            throw new DomainException('Insufficient wallet balance.');
        }

        $wallet->decrement('balance', $amountInPhp);

        $wallet->walletTransactions()->create([
            'amount' => $amountInPhp,
            'type' => 'withdrawal',
            'description' => 'Share capital payment',
            'reference_id' => $payment->id,
            'reference_type' => Payment::class,
        ]);
    }

    private function processGatewayPayment(
        Payment $payment,
        PaymentMethod $paymentMethod,
        array $data
    ): array {
        try {
            $gateway = PaymentGatewayFactory::make($data['gateway'] ?? 'paymongo');
            $gatewayMethodId = $this->resolveGatewayMethodId($gateway, $paymentMethod, $data);

            $amountInPhp = $payment->amount / 100;
            $intent = $gateway->createPaymentIntent($amountInPhp);
            $intentId = data_get($intent, 'data.id')
                ?? throw LoanGatewayException::failedToCreatePaymentIntent(
                    data_get($intent, 'errors')
                );

            $attached = $gateway->attach($intentId, $gatewayMethodId);

            $payment->update([
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
            ]);

            return [
                'payment' => $payment,
                'next_action' => $gateway->getNextAction($attached),
            ];
        } catch (Throwable $e) {
            $payment->update([
                'status_id' => Status::FAILED,
                'gateway_response' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }

    private function resolveGatewayMethodId($gateway, PaymentMethod $method, array $data): string
    {
        if ($method->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw new DomainException('Missing gateway_payment_method_id for client-side method.');
        }

        $response = $gateway->createPaymentMethod($method->gateway_type);

        return data_get($response, 'data.id')
            ?? throw new DomainException('Failed to create payment method.');
    }

    private function hasPendingPayment(ShareCapitalSchedule $schedule): bool
    {
        return $schedule->payments()
            ->where('status_id', Status::PENDING)
            ->where('created_at', '>', now()->subMinutes(1))
            ->exists();
    }
}
