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
            ->get(['id', 'name', 'slug', 'description', 'icon']);
    }

    /**
     * Full fund summary for a year, optionally scoped to one service.
     */
    public function summary(int $year, ?string $serviceSlug = null): array
    {
        $query = DB::table('revenue_breakdowns')
            ->join('allocation_services', 'revenue_breakdowns.allocation_service_id', '=', 'allocation_services.id')
            ->join('allocations', 'allocation_services.allocation_id', '=', 'allocations.id')
            ->join('services', 'allocation_services.service_id', '=', 'services.id')
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
                'allocation_services.value as configured_percentage', // FIXED: changed percentage -> value
                DB::raw('SUM(revenue_breakdowns.amount) as total_amount'),
                DB::raw('COUNT(revenue_breakdowns.id) as transaction_count'),
            ])
            ->groupBy(
                'services.id', 'services.name', 'services.slug', 'services.description', 'services.icon',
                'allocations.id', 'allocations.name', 'allocations.slug', 'allocations.description',
                'allocation_services.id', 'allocation_services.value' // FIXED: changed percentage -> value
            )
            ->get();

        $totalFund = (float) $rows->sum('total_amount');

        // --- Per service breakdown ---
        $services = $rows->groupBy('service_id')->map(function ($group) {
            $first = $group->first();
            $serviceTotal = (float) $group->sum('total_amount');

            $allocations = $group->map(function ($row) use ($serviceTotal) {
                $amount = (float) $row->total_amount;

                return [
                    'id' => $row->allocation_id,
                    'name' => $row->allocation_name,
                    'slug' => $row->allocation_slug,
                    'description' => $row->allocation_description,
                    'configured_percentage' => round((float) $row->configured_percentage * 100, 2),
                    'amount' => round($amount, 2),
                    'actual_percentage' => $serviceTotal > 0
                        ? round(($amount / $serviceTotal) * 100, 2)
                        : 0,
                    'transaction_count' => (int) $row->transaction_count,
                ];
            })->sortByDesc('amount')->values();

            return [
                'id' => $first->service_id,
                'name' => $first->service_name,
                'slug' => $first->service_slug,
                'description' => $first->service_description,
                'icon' => $first->service_icon,
                'total' => round($serviceTotal, 2),
                'allocations' => $allocations,
            ];
        })->sortByDesc('total')->values();

        // --- Grand allocation summary ---
        $allocationsSummary = $rows->groupBy('allocation_id')->map(function ($group) {
            $first = $group->first();
            $amount = (float) $group->sum('total_amount');

            return [
                'id' => $first->allocation_id,
                'name' => $first->allocation_name,
                'slug' => $first->allocation_slug,
                'description' => $first->allocation_description,
                'amount' => round($amount, 2),
            ];
        })->sortByDesc('amount')->values();

        return [
            'year' => $year,
            'service_filter' => $serviceSlug ?: 'all',
            'total_fund' => round($totalFund, 2),
            'total_transactions' => (int) $rows->sum('transaction_count'),
            'services' => $services,
            'allocations' => $allocationsSummary,
        ];
    }
}
