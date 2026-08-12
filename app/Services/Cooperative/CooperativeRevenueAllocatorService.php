<?php

namespace App\Services\Cooperative;

use App\Models\RevenueBreakdown;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CooperativeRevenueAllocatorService
{
    /**
     * Split a paid transaction amount across a service's configured
     * allocations and record each share as a RevenueBreakdown row.
     *
     * $amount must be a plain decimal value (e.g. 500.00), not cents.
     */
    public function allocate(string $serviceSlug, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $service = Service::query()
            ->where('slug', $serviceSlug)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            Log::warning("Cooperative allocation skipped: service [{$serviceSlug}] not found or inactive.");

            return;
        }

        $allocationServices = $service->allocationServices()->get();

        if ($allocationServices->isEmpty()) {
            Log::warning("Cooperative allocation skipped: service [{$serviceSlug}] has no allocations configured.");

            return;
        }

        DB::transaction(function () use ($allocationServices, $amount) {
            $remaining = round($amount, 2);
            $lastIndex = $allocationServices->count() - 1;

            $allocationServices->values()->each(function ($allocationService, $index) use ($amount, &$remaining, $lastIndex) {
                $share = $index === $lastIndex
                    ? $remaining
                    : round($amount * (float) $allocationService->percentage, 2);

                $remaining = round($remaining - $share, 2);

                RevenueBreakdown::create([
                    'allocation_service_id' => $allocationService->id,
                    'amount' => $share,
                ]);
            });
        });
    }
}
