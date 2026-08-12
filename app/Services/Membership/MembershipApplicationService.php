<?php

namespace App\Services\Membership;

use App\Exceptions\Membership\MembershipAlreadyExistsException;
use App\Exceptions\Membership\MembershipCancellationException;
use App\Exceptions\Membership\MembershipInvalidTermException;
use App\Models\MemberMembership;
use App\Models\MembershipSchedule;
use App\Models\MembershipSetting;
use App\Models\Status;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class MembershipApplicationService
{
    public function apply(User $user, int $termMonths): MemberMembership
    {
        $setting = MembershipSetting::current();
        if (! in_array($termMonths, $setting->allowed_term_months)) {
            throw new MembershipInvalidTermException($termMonths, $setting->allowed_term_months);
        }

        $existing = MemberMembership::where('user_id', $user->id)
            ->whereIn('status_id', [Status::APPROVED, Status::ACTIVE])
            ->with(['schedules'])
            ->first();

        if ($existing) {
            if ($existing->status_id === Status::ACTIVE) {
                throw new MembershipAlreadyExistsException('You already have an active membership.');
            }

            $hasActivePayment = $existing->schedules->contains(function ($schedule) {
                return $this->hasPendingPayment($schedule);
            });

            if ($hasActivePayment) {
                throw new MembershipAlreadyExistsException('A payment is currently being processed. Please wait a moment.');
            }

            return $existing;
        }

        return DB::transaction(function () use ($user, $termMonths, $setting) {
            $membership = MemberMembership::create([
                'user_id' => $user->id,
                'status_id' => Status::APPROVED,
                'amount' => $setting->share_capital_amount,
                'term_months' => $termMonths,
            ]);

            $this->generateSchedules($membership);
            $membership->load('schedules');

            // Notify user that the membership application was submitted successfully
            $user->notify(new GeneralNotification(
                type: 'membership_application_submitted',
                title: 'Membership Application Submitted',
                body: 'Your membership application of ₱'.number_format($membership->amount, 2).' has been submitted. Please complete your payment to become a full member.',
                actionType: 'VIEW_MEMBERSHIP',
                route: '/(coop)/',
                extraData: [
                    'membership_id' => $membership->id,
                    'amount' => $membership->amount,
                    'status' => 'approved',
                ]
            ));

            return $membership;
        });
    }

    /**
     * Initiates payment. If an old pending payment exists (> 1 min), it cancels it
     * and allows a new request. If it's < 1 min, it blocks to prevent spam.
     */
    public function initiateSchedulePayment(User $user, MembershipSchedule $schedule, int $paymentMethodId)
    {
        $schedule->loadMissing('membership');
        $membership = $schedule->membership;

        if (! $membership) {
            throw new \Exception('Membership record not found for this schedule.', 404);
        }

        if ((int) $membership->user_id !== (int) $user->id) {
            throw new \Exception('Unauthorized', 403);
        }

        // 1. Check for VERY recent payments (spam protection)
        if ($this->hasPendingPayment($schedule)) {
            throw new \Exception('A payment is already pending. Please wait 1 minute before requesting a new link.', 422);
        }

        // 2. Cleanup: Cancel any STALE pending payments for this schedule
        // This ensures the user can try again if they backed out of a previous session
        $schedule->payments()
            ->where('status_id', Status::PENDING)
            ->update(['status_id' => Status::CANCELLED]);

        $paymentService = app(MembershipPaymentService::class);

        return $paymentService->initiate(
            schedule: $schedule,
            paymentMethodId: $paymentMethodId,
        );
    }

    public function cancel(User $user): void
    {
        $membership = MemberMembership::where('user_id', $user->id)
            ->whereIn('status_id', [Status::APPROVED, Status::ACTIVE])
            ->first();

        if (! $membership) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'No active or pending membership found to cancel.',
                ], 404)
            );
        }

        if ($membership->status_id === Status::ACTIVE) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel an active membership.',
                ], 422)
            );
        }

        $isProcessing = $membership->schedules->contains(function ($schedule) {
            return $this->hasPendingPayment($schedule);
        });

        if ($isProcessing) {
            throw new MembershipCancellationException(
                'Cannot cancel while a payment is being processed.'
            );
        }

        $membership->update(['status_id' => Status::CANCELLED]);
        $membership->schedules()->update(['status_id' => Status::CANCELLED]);

        $membership->schedules->each(function ($schedule) {
            $schedule->payments()
                ->whereNotIn('status_id', [Status::PAID])
                ->update(['status_id' => Status::CANCELLED]);
        });
    }

    public function hasPendingPayment(MembershipSchedule $schedule): bool
    {
        return $schedule->payments()
            ->where('status_id', Status::PENDING)
            ->where('created_at', '>', now()->subMinutes(1))
            ->exists();
    }

    private function ensureNoActiveMembership(User $user): void
    {
        $existing = MemberMembership::where('user_id', $user->id)
            ->whereIn('status_id', [Status::APPROVED, Status::ACTIVE])
            ->first();

        if (! $existing) {
            return;
        }

        if ($existing->status_id === Status::ACTIVE) {
            throw new MembershipAlreadyExistsException('You already have an active membership.');
        }

        $hasFreshPayment = $existing->schedules->contains(function ($schedule) {
            return $this->hasPendingPayment($schedule);
        });

        if ($hasFreshPayment) {
            throw new MembershipAlreadyExistsException('You already have a pending membership with a payment in progress.');
        }

        $existing->update(['status_id' => Status::CANCELLED]);
        $existing->schedules()->update(['status_id' => Status::CANCELLED]);
    }

    private function generateSchedules(MemberMembership $membership): void
    {
        $total = $membership->amount;
        $months = $membership->term_months;
        $installment = (int) floor($total / $months);
        $remainder = $total - ($installment * $months);

        $schedules = [];

        for ($i = 1; $i <= $months; $i++) {
            $schedules[] = [
                'member_membership_id' => $membership->id,
                'status_id' => Status::UNPAID,
                'installment_no' => $i,
                'amount' => $i === $months ? $installment + $remainder : $installment,
                'due_date' => now()->addMonths($i)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        MembershipSchedule::insert($schedules);
    }
}
