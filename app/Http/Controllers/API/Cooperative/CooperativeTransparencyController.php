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
     * Resolve the logged-in member's cooperative, or abort with 403.
     * The ID always comes from the authenticated user, never from the request.
     */
    private function cooperativeId(Request $request): int
    {
        $cooperativeId = $request->user()?->cooperative_id;

        abort_if(
            ! $cooperativeId,
            403,
            'Your account is not linked to a cooperative.'
        );

        return (int) $cooperativeId;
    }

    /**
     * Years that have recorded fund activity for the member's cooperative.
     *
     * @tags Cooperative > Transparency
     *
     * @response scenario="success" {
     *   "success": true,
     *   "data": ["2026", "2025", "2024"]
     * }
     */
    public function years(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->transparencyService->availableYears(
                $this->cooperativeId($request)
            ),
        ]);
    }

    /**
     * Active services usable as the module/service filter.
     *
     * @tags Cooperative > Transparency
     */
    public function services(Request $request): JsonResponse
    {
        $this->cooperativeId($request); // ensure the member belongs to a coop

        return response()->json([
            'success' => true,
            'data' => $this->transparencyService->activeServices(),
        ]);
    }

    /**
     * Fund summary for a year, scoped to the member's own cooperative.
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
            cooperativeId: $this->cooperativeId($request),
            year: (int) $validated['year'],
            serviceSlug: $validated['service'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
