<?php

namespace App\Services\Cooperative;

use App\Models\RevenueBreakdown;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CooperativeTransparencyService
{
    /**
     * Years that actually have recorded revenue (for the year filter).
     */
    public function availableYears(): array
    {
        return RevenueBreakdown::query()
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->map(fn ($date) => (string) Carbon::parse($date)->year)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Active services for the module/service filter.
     */
    public function activeServices()
    {
        return Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'icon',
            ]);
    }

    /**
     * Normalize allocation type.
     *
     * Only PHP and PERCENTAGE are supported.
     */
    private function normalizeAllocationType(?string $type): string
    {
        $type = strtoupper(trim((string) $type));

        return $type === 'PHP'
            ? 'PHP'
            : 'PERCENTAGE';
    }

    /**
     * Normalize configured allocation value.
     *
     * PHP:
     *   100     = ₱100
     *
     * PERCENTAGE:
     *   0.25    = 25%
     *   0.2500  = 25%
     *   25      = 25%
     *   1.00    = 100%
     */
    private function normalizeConfiguredValue(
        float $value,
        string $type
    ): float {
        if ($type === 'PHP') {
            return round(max($value, 0), 2);
        }

        // Decimal percentage:
        // 0.25 = 25%
        // 1.00 = 100%
        if ($value > 0 && $value <= 1) {
            return round($value * 100, 2);
        }

        return round(max($value, 0), 2);
    }

    /**
     * Calculate allocations using PHP-first priority.
     *
     * Example:
     *
     * Total fund = ₱500
     *
     * PHP:
     *   ₱100
     *   ₱100
     *
     * Remaining:
     *   ₱300
     *
     * Percentage:
     *   100% = ₱300
     *   50%  = ₱150
     *   25%  = ₱75
     */
    private function calculateAllocations(
        $group,
        float $serviceTotal
    ) {
        $remaining = round(max($serviceTotal, 0), 2);

        /*
         * ============================================================
         * STEP 1: Prepare allocation configuration
         * ============================================================
         */
        $configuredAllocations = $group->map(function ($row) {
            $type = $this->normalizeAllocationType(
                $row->allocation_type
            );

            $configuredValue = $this->normalizeConfiguredValue(
                (float) $row->raw_configured_value,
                $type
            );

            return [
                'row' => $row,
                'type' => $type,
                'configured_value' => $configuredValue,
            ];
        });

        /*
         * ============================================================
         * STEP 2: PHP allocations FIRST
         * ============================================================
         *
         * PHP allocations always deduct from the original fund first.
         */
        $allocations = [];

        foreach ($configuredAllocations as $configured) {
            if ($configured['type'] !== 'PHP') {
                continue;
            }

            $row = $configured['row'];
            $configuredValue = $configured['configured_value'];

            /*
             * Do not allow a PHP allocation to make the remaining
             * balance negative.
             */
            $amount = min(
                $configuredValue,
                $remaining
            );

            $amount = round(max($amount, 0), 2);

            $allocations[$row->allocation_service_id] = [
                'id' => $row->allocation_id,
                'name' => $row->allocation_name,
                'slug' => $row->allocation_slug,
                'description' => $row->allocation_description,
                'type' => 'PHP',
                'configured_value' => $configuredValue,
                'configured_percentage' => $serviceTotal > 0
                    ? round(($configuredValue / $serviceTotal) * 100, 2)
                    : 0,
                'amount' => $amount,
                'actual_percentage' => $serviceTotal > 0
                    ? round(($amount / $serviceTotal) * 100, 2)
                    : 0,
                'transaction_count' => (int) $row->transaction_count,
            ];

            $remaining = round(
                max($remaining - $amount, 0),
                2
            );
        }

        /*
         * ============================================================
         * STEP 3: PERCENTAGE allocations SECOND
         * ============================================================
         *
         * Percentages are applied ONLY to the remaining balance.
         *
         * Example:
         *
         * ₱500 total
         * - ₱100 PHP
         * - ₱100 PHP
         * = ₱300 remaining
         *
         * 50% = ₱150
         */
        foreach ($configuredAllocations as $configured) {
            if ($configured['type'] !== 'PERCENTAGE') {
                continue;
            }

            $row = $configured['row'];
            $configuredPercentage = $configured['configured_value'];

            /*
             * Prevent percentages above 100%.
             */
            $percentage = min(
                max($configuredPercentage, 0),
                100
            );

            $amount = round(
                $remaining * ($percentage / 100),
                2
            );

            $allocations[$row->allocation_service_id] = [
                'id' => $row->allocation_id,
                'name' => $row->allocation_name,
                'slug' => $row->allocation_slug,
                'description' => $row->allocation_description,
                'type' => 'PERCENTAGE',
                'configured_value' => $percentage,
                'configured_percentage' => $percentage,
                'amount' => $amount,
                'actual_percentage' => $serviceTotal > 0
                    ? round(($amount / $serviceTotal) * 100, 2)
                    : 0,
                'transaction_count' => (int) $row->transaction_count,
            ];
        }

        return collect($allocations)
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * Full fund summary for a year, optionally scoped to one service.
     */
    public function summary(
        int $year,
        ?string $serviceSlug = null
    ): array {
        $query = DB::table('revenue_breakdowns')
            ->join(
                'allocation_services',
                'revenue_breakdowns.allocation_service_id',
                '=',
                'allocation_services.id'
            )
            ->join(
                'allocations',
                'allocation_services.allocation_id',
                '=',
                'allocations.id'
            )
            ->join(
                'services',
                'allocation_services.service_id',
                '=',
                'services.id'
            )
            ->whereYear(
                'revenue_breakdowns.created_at',
                $year
            );

        if ($serviceSlug && $serviceSlug !== 'all') {
            $query->where(
                'services.slug',
                $serviceSlug
            );
        }

        $rows = $query
            ->select([
                'services.id as service_id',
                'services.name as service_name',
                'services.slug as service_slug',
                'services.description as service_description',
                'services.icon as service_icon',

                'allocations.id as allocation_id',
                'allocations.name as allocation_name',
                'allocations.slug as allocation_slug',
                'allocations.description as allocation_description',

                'allocation_services.id as allocation_service_id',
                'allocation_services.type as allocation_type',
                'allocation_services.value as raw_configured_value',

                DB::raw(
                    'SUM(revenue_breakdowns.amount) as total_amount'
                ),

                DB::raw(
                    'COUNT(revenue_breakdowns.id) as transaction_count'
                ),
            ])
            ->groupBy(
                'services.id',
                'services.name',
                'services.slug',
                'services.description',
                'services.icon',

                'allocations.id',
                'allocations.name',
                'allocations.slug',
                'allocations.description',

                'allocation_services.id',
                'allocation_services.type',
                'allocation_services.value'
            )
            ->get();

        /*
         * ============================================================
         * GRAND TOTAL FUND
         * ============================================================
         */
        $totalFund = (float) $rows->sum('total_amount');

        /*
         * ============================================================
         * PER SERVICE BREAKDOWN
         * ============================================================
         */
        $services = $rows
            ->groupBy('service_id')
            ->map(function ($group) {
                $first = $group->first();

                /*
                 * This is the total fund available for this service.
                 *
                 * Example:
                 * ₱500
                 */
                $serviceTotal = round(
                    (float) $group->sum('total_amount'),
                    2
                );

                /*
                 * Calculate allocations using:
                 *
                 * PHP FIRST
                 * ↓
                 * remaining balance
                 * ↓
                 * PERCENTAGE
                 */
                $allocations = $this->calculateAllocations(
                    $group,
                    $serviceTotal
                );

                return [
                    'id' => $first->service_id,
                    'name' => $first->service_name,
                    'slug' => $first->service_slug,
                    'description' => $first->service_description,
                    'icon' => $first->service_icon,

                    'total' => $serviceTotal,

                    'allocations' => $allocations,
                ];
            })
            ->sortByDesc('total')
            ->values();

        /*
         * ============================================================
         * GRAND ALLOCATION SUMMARY
         * ============================================================
         *
         * Do not use the raw revenue_breakdowns.amount here because
         * the actual allocation amount is calculated based on:
         *
         * PHP first → remaining → percentage.
         */
        $allocationsSummary = collect();

        foreach ($services as $service) {
            foreach ($service['allocations'] as $allocation) {
                $allocationId = $allocation['id'];

                if (! $allocationsSummary->has($allocationId)) {
                    $allocationsSummary->put(
                        $allocationId,
                        [
                            'id' => $allocation['id'],
                            'name' => $allocation['name'],
                            'slug' => $allocation['slug'],
                            'description' => $allocation['description'],
                            'type' => $allocation['type'],
                            'amount' => 0,
                        ]
                    );
                }

                $current = $allocationsSummary->get(
                    $allocationId
                );

                $current['amount'] = round(
                    $current['amount'] + $allocation['amount'],
                    2
                );

                $allocationsSummary->put(
                    $allocationId,
                    $current
                );
            }
        }

        $allocationsSummary = $allocationsSummary
            ->sortByDesc('amount')
            ->values();

        return [
            'year' => $year,

            'service_filter' => $serviceSlug ?: 'all',

            'total_fund' => round(
                $totalFund,
                2
            ),

            'total_transactions' => (int) $rows->sum(
                'transaction_count'
            ),

            'services' => $services,

            'allocations' => $allocationsSummary,
        ];
    }
}
