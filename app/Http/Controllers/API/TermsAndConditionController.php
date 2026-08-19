<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\JsonResponse;

class TermsAndConditionController extends Controller
{
    /**
     * Get the Terms and Conditions.
     *
     * @tags Terms and Conditions
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Terms and Conditions of Use",
     *   "content": "TERMS AND CONDITIONS OF USE..."
     * }
     */
    public function show(): JsonResponse
    {
        $terms = TermsAndCondition::query()
            ->latest('id')
            ->first();

        if (! $terms) {
            return response()->json([
                'message' => 'Terms and Conditions not found.',
            ], 404);
        }

        return response()->json([
            'id' => $terms->id,
            'name' => $terms->name,
            'content' => $terms->content,
        ]);
    }
}
