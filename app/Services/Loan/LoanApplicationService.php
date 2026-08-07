<?php

namespace App\Services\Loan;

use App\Exceptions\Loan\LoanLimitExceededException;
use App\Models\Loan;
use App\Models\Status;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\DB;

class LoanApplicationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected LoanService $loanService
    ) {}

    /**
     * Summary of apply
     * Applies for a new loan for the given user with the provided data.
     * Validates the requested amount against the user's loanable limit and computes the loan schedule.
     *
     * @throws LoanLimitExceededException if the requested amount exceeds the user's loanable limit.
     */
    public function apply(User $user, array $data): Loan
    {
        return DB::transaction(function () use ($user, $data) {

            $loanableAmount = $this->loanService->getLoanableAmount($user);

            if ($data['amount'] > $loanableAmount) {
                throw new LoanLimitExceededException($loanableAmount);
            }

            $computed = $this->loanService->computeSchedule(
                amount: $data['amount'],
                term: $data['term'],
                startDate: $data['start_date'] ?? null,
                user: $user,
            );

            $firstSchedule = $computed['schedule'][0] ?? null;

            if (! $firstSchedule) {
                throw new \RuntimeException('Invalid loan schedule generated.');
            }

            $loan = Loan::create([
                'user_id' => $user->id,
                'status_id' => Status::PENDING,
                'amount' => $data['amount'],
                'interest_rate_display' => $computed['interest_rate_display'],
                'interest_rate' => $computed['interest_rate'],
                'term_months' => $data['term'],
                'monthly_principal' => $firstSchedule['principal'],
                'start_date' => $computed['release_date'],
                'end_date' => now()->addMonths($data['term'])->toDateString(),
            ]);

            foreach ($computed['schedule'] as $item) {
                $loan->loanSchedules()->create([
                    'status_id' => Status::UNPAID,
                    'month_no' => $item['month'],
                    'beginning_balance' => $item['beginning_balance'],
                    'interest_amount' => $item['interest'],
                    'principal_amount' => $item['principal'],
                    'total_payment' => $item['monthly_payment'],
                    'ending_balance' => $item['ending_balance'],
                    'due_date' => $item['due_date'],
                ]);
            }

            // Notify user that the loan application was submitted successfully
            $user->notify(new GeneralNotification(
                type: 'loan_application_submitted',
                title: 'Loan Application Submitted',
                body: 'Your loan application of ₱'.number_format($loan->amount, 2).' has been submitted. Please wait for approval.',
                actionType: 'VIEW_LOAN',
                route: "/(loan)/breakdown?id={$loan->id}&include=user,status,loanSchedules,loanPayments",
                extraData: [
                    'loan_id' => $loan->id,
                    'amount' => $loan->amount,
                    'status' => 'pending',
                ]
            ));

            return $loan;
        });
    }
}
