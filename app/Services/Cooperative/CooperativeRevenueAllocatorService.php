<?php

namespace App\Services\Cooperative;

use App\Models\RevenueBreakdown;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CooperativeRevenueAllocatorService
{
    /**
     * Split a paid transaction amount across a service's configured allocations.
     * Fixed (PHP) allocations take priority and deduct flat amounts first.
     * Percentage allocations calculate their share based on the remaining balance.
     *
     * @param  float  $amount  Plain decimal value (e.g., 500.00)
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

        // Fetch allocations sorted by priority (1, 2, 3...)
        $allocationServices = $service->allocationServices()
            ->orderBy('priority', 'asc')
            ->get();

        if ($allocationServices->isEmpty()) {
            Log::warning("Cooperative allocation skipped: service [{$serviceSlug}] has no allocations configured.");

            return;
        }

        DB::transaction(function () use ($allocationServices, $amount) {
            $totalAmount = round($amount, 2);
            $remainingBalance = $totalAmount;

            // Separate allocations by type
            $fixedAllocations = $allocationServices->filter(function ($item) {
                return strtoupper(trim($item->type)) === 'PHP' || strtoupper(trim($item->type)) === 'FIXED';
            });

            $percentageAllocations = $allocationServices->filter(function ($item) {
                return strtoupper(trim($item->type)) !== 'PHP' && strtoupper(trim($item->type)) !== 'FIXED';
            });

            // PHASE 1: Process Fixed (PHP) Allocations First by Priority
            foreach ($fixedAllocations as $allocationService) {
                $targetAmount = (float) $allocationService->value;

                // Take requested amount or whatever is left if balance is insufficient
                $share = min($remainingBalance, $targetAmount);
                $share = max(0, round($share, 2));

                $remainingBalance = round($remainingBalance - $share, 2);

                RevenueBreakdown::create([
                    'allocation_service_id' => $allocationService->id,
                    'amount' => $share,
                ]);
            }

            // PHASE 2: Process Percentage Allocations on the Remaining Pool
            if ($percentageAllocations->isNotEmpty() && $remainingBalance > 0) {
                $poolForPercentages = $remainingBalance;
                $percentageTracker = $poolForPercentages;
                $lastIndex = $percentageAllocations->count() - 1;

                $percentageAllocations->values()->each(function ($allocationService, $index) use ($poolForPercentages, &$percentageTracker, $lastIndex) {
                    $configuredPercentage = (float) $allocationService->value; // e.g., 0.20 or 20

                    // Support both decimal rate (0.20) and integer percentage (20)
                    $rate = $configuredPercentage > 1 ? ($configuredPercentage / 100) : $configuredPercentage;

                    $share = ($index === $lastIndex)
                        ? $percentageTracker
                        : round($poolForPercentages * $rate, 2);

                    $share = min($percentageTracker, max(0, $share));
                    $percentageTracker = round($percentageTracker - $share, 2);

                    RevenueBreakdown::create([
                        'allocation_service_id' => $allocationService->id,
                        'amount' => $share,
                    ]);
                });
            }
        });
    }
}
