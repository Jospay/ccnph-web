<?php

namespace App\Exceptions\Membership;

use Exception;
use Illuminate\Http\JsonResponse;

class MembershipInvalidTermException extends Exception
{
    public function __construct(int $termMonths, array $allowedTerms)
    {
        parent::__construct(
            "Term of {$termMonths} months is not allowed. Allowed terms: ".implode(', ', $allowedTerms).'.'
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
