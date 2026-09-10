<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AdResource;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    /**
     * Display a listing of active ads ordered by banner position.
     */
    public function index(): JsonResponse
    {
        $ads = Ad::where('is_active', true)
            ->orderBy('sort_banner', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ads retrieved successfully.',
            'data' => AdResource::collection($ads),
        ]);
    }
}
