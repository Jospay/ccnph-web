<?php

namespace App\Exceptions\Loan;

use App\Models\LoanSchedule;
use Exception;

class LoanPendingPaymentExistsException extends Exception
{
    public function __construct(
        public readonly LoanSchedule $schedule,
    ) {
        parent::__construct('A payment is already pending. Please wait 1 minute before requesting a new link.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'schedule_id' => $this->schedule->id,
            ],
        ], 409);
    }
}
