<?php

namespace App\Exceptions\Membership;

use Exception;
use Illuminate\Http\JsonResponse;

class MembershipCancellationException extends Exception
{
    public function __construct(
        string $message = 'Cannot cancel this membership.',
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
