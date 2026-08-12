<?php

namespace App\Http\Controllers\API\Cooperative;

use App\Http\Controllers\Controller;
use App\Services\Cooperative\CooperativeTransparencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CooperativeTransparencyController extends Controller
{
    public function __construct(
        private readonly CooperativeTransparencyService $transparencyService
    ) {}

    /**
     * Years that have recorded cooperative fund activity.
     *
     * @tags Cooperative > Transparency
     *
     * @response scenario="success" {
     *   "success": true,
     *   "data": ["2026", "2025", "2024"]
     * }
     */
    public function years(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->transparencyService->availableYears(),
        ]);
    }

    /**
     * Active services usable as the module/service filter.
     *
     * @tags Cooperative > Transparency
     */
    public function services(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->transparencyService->activeServices(),
        ]);
    }

    /**
     * Fund summary for a year, optionally scoped to one service.
     * Returns per-service allocation breakdown (configured vs actual
     * percentage) plus a grand allocation summary + total fund.
     *
     * @tags Cooperative > Transparency
     *
     * @queryParam year integer required Year to summarize. Example: 2026
     * @queryParam service string optional Service slug, or "all". Example: coop-membership
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4'],
            'service' => ['nullable', 'string'],
        ]);

        $summary = $this->transparencyService->summary(
            (int) $validated['year'],
            $validated['service'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
