<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use Illuminate\Http\JsonResponse;

class PrivacyPolicyController extends Controller
{
    /**
     * Get the Privacy Policy.
     *
     * @tags Privacy Policy
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "PRIVACY POLICY",
     *   "content": "COOPERATIVES COOPERATION NETWORK PHILIPPINES..."
     * }
     */
    public function show(): JsonResponse
    {
        $policy = PrivacyPolicy::query()
            ->latest('id')
            ->first();

        if (! $policy) {
            return response()->json([
                'message' => 'Privacy Policy not found.',
            ], 404);
        }

        return response()->json([
            'id' => $policy->id,
            'name' => $policy->name,
            'content' => $policy->content,
        ]);
    }
}