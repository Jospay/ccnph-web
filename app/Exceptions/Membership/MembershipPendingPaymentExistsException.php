<?php

namespace App\Exceptions\Membership;

use App\Models\MembershipSchedule;
use Exception;

class MembershipPendingPaymentExistsException extends Exception
{
    public function __construct(
        public readonly MembershipSchedule $schedule,
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
