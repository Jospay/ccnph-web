<?php

namespace App\Services\Cooperative;

use App\Models\Cooperative;
use App\Models\RevenueBreakdown;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CooperativeTransparencyService
{
    /**
     * Years that have recorded revenue for this user inside this cooperative.
     */
    public function availableYears(int $cooperativeId, int $userId): array
    {
        return RevenueBreakdown::query()
            ->where('cooperative_id', $cooperativeId)
            ->where('user_id', $userId)
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
            ->where('is_active_transparency', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'icon']);
    }

    /**
     * Only PHP and PERCENTAGE are supported.
     */
    private function normalizeAllocationType(?string $type): string
    {
        return strtoupper(trim((string) $type)) === 'PHP'
            ? 'PHP'
            : 'PERCENTAGE';
    }

    /**
     * PHP:        100  = ₱100
     * PERCENTAGE: 0.25 = 25%, 25 = 25%, 1.00 = 100%
     */
    private function normalizeConfiguredValue(float $value, string $type): float
    {
        if ($type === 'PHP') {
            return round(max($value, 0), 2);
        }

        if ($value > 0 && $value <= 1) {
            return round($value * 100, 2);
        }

        return round(max($value, 0), 2);
    }

    /**
     * Build allocation rows from the ACTUAL recorded amounts.
     *
     * Every revenue_breakdowns row already holds the amount that went to
     * its allocation, so we simply SUM it:
     *   - PHP:        ₱100 x 5 txns = ₱500
     *   - PERCENTAGE: sum of each txn's recorded share
     * The allocations therefore always add up to the service total.
     */
    private function buildAllocations($group, float $serviceTotal)
    {
        return $group
            ->map(function ($row) use ($serviceTotal) {
                $type = $this->normalizeAllocationType($row->allocation_type);

                $configuredValue = $this->normalizeConfiguredValue(
                    (float) $row->raw_configured_value,
                    $type
                );

                $amount = round((float) $row->total_amount, 2);
                $count = (int) $row->transaction_count;

                return [
                    'id' => $row->allocation_id,
                    'name' => $row->allocation_name,
                    'slug' => $row->allocation_slug,
                    'description' => $row->allocation_description,
                    'type' => $type,
                    'configured_value' => $configuredValue,
                    // For PHP there is no configured %, so show the real share instead
                    'configured_percentage' => $type === 'PERCENTAGE'
                        ? $configuredValue
                        : ($serviceTotal > 0
                            ? round(($amount / $serviceTotal) * 100, 2)
                            : 0),
                    'amount' => $amount,
                    'actual_percentage' => $serviceTotal > 0
                        ? round(($amount / $serviceTotal) * 100, 2)
                        : 0,
                    'transaction_count' => $count,
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * Fund summary for a year, scoped to ONE cooperative AND ONE user,
     * optionally to one service.
     */
    public function summary(
        int $cooperativeId,
        int $userId,
        int $year,
        ?string $serviceSlug = null
    ): array {
        $query = DB::table('revenue_breakdowns')
            ->join('allocation_services', 'revenue_breakdowns.allocation_service_id', '=', 'allocation_services.id')
            ->join('allocations', 'allocation_services.allocation_id', '=', 'allocations.id')
            ->join('services', 'allocation_services.service_id', '=', 'services.id')
            ->where('revenue_breakdowns.cooperative_id', $cooperativeId)
            ->where('revenue_breakdowns.user_id', $userId) // only the user's own records
            ->whereYear('revenue_breakdowns.created_at', $year);

        if ($serviceSlug && $serviceSlug !== 'all') {
            $query->where('services.slug', $serviceSlug);
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

                DB::raw('SUM(revenue_breakdowns.amount) as total_amount'),
                DB::raw('COUNT(revenue_breakdowns.id) as transaction_count'),
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

        $totalFund = round((float) $rows->sum('total_amount'), 2);

        /* PER SERVICE */
        $services = $rows
            ->groupBy('service_id')
            ->map(function ($group) {
                $first = $group->first();

                $serviceTotal = round((float) $group->sum('total_amount'), 2);

                return [
                    'id' => $first->service_id,
                    'name' => $first->service_name,
                    'slug' => $first->service_slug,
                    'description' => $first->service_description,
                    'icon' => $first->service_icon,
                    'total' => $serviceTotal,
                    'allocations' => $this->buildAllocations($group, $serviceTotal),
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* GRAND ALLOCATION SUMMARY (sum of the per-service amounts) */
        $allocationsSummary = collect();

        foreach ($services as $service) {
            foreach ($service['allocations'] as $allocation) {
                $key = $allocation['id'];

                $current = $allocationsSummary->get($key, [
                    'id' => $allocation['id'],
                    'name' => $allocation['name'],
                    'slug' => $allocation['slug'],
                    'description' => $allocation['description'],
                    'type' => $allocation['type'],
                    'amount' => 0,
                ]);

                $current['amount'] = round($current['amount'] + $allocation['amount'], 2);

                $allocationsSummary->put($key, $current);
            }
        }

        $allocationsSummary = $allocationsSummary->sortByDesc('amount')->values();

        $cooperative = Cooperative::query()
            ->select(['id', 'name'])
            ->find($cooperativeId);

        return [
            'cooperative' => [
                'id' => $cooperativeId,
                'name' => $cooperative?->name,
            ],
            'year' => $year,
            'service_filter' => $serviceSlug ?: 'all',
            'total_fund' => $totalFund,
            'total_transactions' => (int) $rows->sum('transaction_count'),
            'services' => $services,
            'allocations' => $allocationsSummary,
        ];
    }
}
