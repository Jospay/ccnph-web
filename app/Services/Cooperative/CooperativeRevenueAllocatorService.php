<?php

namespace App\Services\Cooperative;

use App\Models\RevenueBreakdown;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CooperativeRevenueAllocatorService
{
    /**
     * Split a paid transaction amount across a service's configured allocations.
     * Fixed (PHP) allocations take priority and deduct flat amounts first.
     * Percentage allocations calculate their share based on the remaining balance.
     *
     * @param  float  $amount  Plain decimal value (e.g., 500.00)
     * @param  Model  $revenueable  The record that generated the revenue (Payment, OrderItem, ...)
     * @param  int|null  $userId  The user who paid
     */
    public function allocate(
        string $serviceSlug,
        float $amount,
        Model $revenueable,
        ?int $userId,
        ?int $cooperativeId = null,
    ): void {
        if ($amount <= 0) {
            return;
        }

        // Duplicate guard: never book the same record twice
        $alreadyAllocated = RevenueBreakdown::query()
            ->where('revenueable_type', $revenueable->getMorphClass())
            ->where('revenueable_id', $revenueable->getKey())
            ->exists();

        if ($alreadyAllocated) {
            return;
        }

        $service = Service::query()
            ->where('slug', $serviceSlug)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            return;
        }

        $allocationServices = $service->allocationServices()
            ->orderBy('priority', 'asc')
            ->get();

        DB::transaction(function () use ($allocationServices, $amount, $cooperativeId, $revenueable, $userId) {
            $totalAmount = round($amount, 2);
            $remainingBalance = $totalAmount;

            // Shared attributes for every breakdown row created for this record
            $base = [
                'revenueable_type' => $revenueable->getMorphClass(),
                'revenueable_id' => $revenueable->getKey(),
                'cooperative_id' => $cooperativeId,
                'user_id' => $userId,
            ];

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

                RevenueBreakdown::create($base + [
                    'allocation_service_id' => $allocationService->id,
                    'amount' => $share,
                ]);
            }

            // PHASE 2: Process Percentage Allocations on the Remaining Pool
            if ($percentageAllocations->isNotEmpty() && $remainingBalance > 0) {
                $poolForPercentages = $remainingBalance;
                $percentageTracker = $poolForPercentages;
                $lastIndex = $percentageAllocations->count() - 1;

                $percentageAllocations->values()->each(function ($allocationService, $index) use ($poolForPercentages, &$percentageTracker, $lastIndex, $base) {
                    $configuredPercentage = (float) $allocationService->value; // e.g., 0.20 or 20

                    // Support both decimal rate (0.20) and integer percentage (20)
                    $rate = $configuredPercentage > 1 ? ($configuredPercentage / 100) : $configuredPercentage;

                    $share = ($index === $lastIndex)
                        ? $percentageTracker
                        : round($poolForPercentages * $rate, 2);

                    $share = min($percentageTracker, max(0, $share));
                    $percentageTracker = round($percentageTracker - $share, 2);

                    RevenueBreakdown::create($base + [
                        'allocation_service_id' => $allocationService->id,
                        'amount' => $share,
                    ]);
                });
            }
        });
    }
}
