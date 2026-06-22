<?php

namespace App\Services\Loan;

use App\Models\Loan;
use App\Models\LoanSetting;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class LoanService
{
    // public function getLoanableAmount(User $user): float
    // {
    //     $loanSetting = $user->getActiveLoanSetting();

    //     if (!$loanSetting) {
    //         throw new Exception('Loan setting not configured.');
    //     }

    //     $totalLoanAmount = $this->getTotalActiveLoanAmount($user);

    //     return max($loanSetting->default_amount - $totalLoanAmount, 0);
    // }
    public function getLoanableAmount(User $user): float
    {
        $loanSetting = $user->getActiveLoanSetting();

        if (!$loanSetting instanceof LoanSetting) {
            return 0;
        }

        $totalLoanAmount = $this->getTotalActiveLoanAmount($user);

        return max($loanSetting->default_amount - $totalLoanAmount, 0);
    }

    public function getUserLoans(User $user)
    {
        // return Loan::where('user_id', $user->id)
        //     ->whereIn('status_id', [Status::ACTIVE, Status::FINISHED])
        //     ->get();
        return Loan::where('user_id', $user->id)
            ->whereIn('status_id', [
                Status::ACTIVE,
                Status::FINISHED,
                Status::PENDING,
                Status::REJECTED
            ])
            ->orderByRaw("status_id = ? DESC", [Status::ACTIVE])
            ->get();

        //         return Loan::where('user_id', $user->id)
        // ->whereIn('status_id', [Status::ACTIVE, Status::FINISHED])
        // ->orderByRaw("
        //     CASE
        //         WHEN status_id = ? THEN 0
        //         WHEN status_id = ? THEN 1
        //         ELSE 2
        //     END
        // ", [Status::ACTIVE, Status::FINISHED])
        // ->get();
    }

    public function computeSchedule(float $amount, int $term, ?string $startDate, ?User $user = null): array
    {
        $setting = $user
            ? ($user->getActiveLoanSetting() ?? LoanSetting::firstOrFail())
            : LoanSetting::firstOrFail();

        $scale = 10;
        $amount = number_format($amount, 10, '.', '');
        $rate = number_format((float) $setting->default_interest_rate, 10, '.', '');
        $principal = bcdiv($amount, (string) $term, $scale);
        $start = Carbon::parse($startDate ?? now())->startOfDay();

        $balance = $amount;
        $totalPayment = '0';
        $schedule = [];

        for ($month = 1; $month <= $term; $month++) {
            $beginningBalance = $balance;
            $dueDate = $start->copy()->addMonths($month);
            $interest = bcmul($balance, $rate, $scale);
            $monthlyPayment = bcadd($principal, $interest, $scale);
            $balance = bcsub($balance, $principal, $scale);
            $totalPayment = bcadd($totalPayment, $monthlyPayment, $scale);

            $schedule[] = [
                'month' => $month,
                'due_date' => $dueDate->format('M d, Y'),
                'principal' => number_format((float) $principal, 2, '.', ''),
                'interest' => number_format((float) $interest, 2, '.', ''),
                'monthly_payment' => number_format((float) $monthlyPayment, 2, '.', ''),
                'beginning_balance' => number_format((float) $beginningBalance, 2, '.', ''),
                'ending_balance' => number_format((float) max((float) $balance, 0), 2, '.', ''),
            ];
        }

        return [
            'loan_amount' => number_format((float) $amount, 2, '.', ''),
            'term_months' => $term,
            'interest_rate_display' => number_format((float) $rate * 100, 2) . '% per month',
            'interest_rate' => $setting->default_interest_rate,
            'release_date' => $start->format('Y-m-d'),
            'total_payment' => number_format((float) $totalPayment, 2, '.', ''),
            'schedule' => $schedule,
        ];
    }

    private function getTotalActiveLoanAmount(User $user): float
    {
        return (float) Loan::where('user_id', $user->id)
            ->whereIn('status_id', [Status::PENDING, Status::ACTIVE])
            ->sum('amount');
    }
}
