<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class SalesAnalyticsController extends Controller
{
    public function analytics(Request $request)
    {
        $user = $request->user()->loadMissing('store');

        if (! $user->store) {
            return redirect()->route('seller.store.create');
        }

        $store = $user->store;

        return Inertia::render('seller/sales/Analytics', [
            'store' => $store,
            'salesSummary' => $this->getSalesSummary($store),
            'topProducts' => $this->getTopProducts($store),
            'ordersOverview' => $this->getOrdersOverview($store),
            'orderStatusBreakdown' => $this->getOrderStatusBreakdown($store),
        ]);
    }

    /**
     * Today / weekly / monthly / yearly totals from completed orders.
     */
    private function getSalesSummary(Store $store): array
    {
        $now = Carbon::now();
        $completed = fn () => $store->orders()->where('status', OrderStatus::COMPLETED);

        return [
            'today' => (float) $completed()
                ->whereDate('completed_at', $now->toDateString())
                ->sum('total'),

            'weekly' => (float) $completed()
                ->whereBetween('completed_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->sum('total'),

            'monthly' => (float) $completed()
                ->whereYear('completed_at', $now->year)
                ->whereMonth('completed_at', $now->month)
                ->sum('total'),

            'yearly' => (float) $completed()
                ->whereYear('completed_at', $now->year)
                ->sum('total'),
        ];
    }

    /**
     * Top 10 best-selling products for this store.
     */
    private function getTopProducts(Store $store): array
    {
        return $store->products()
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get(['id', 'name', 'sold_count'])
            ->map(fn ($product) => [
                'key' => (string) $product->id,
                'label' => $product->name,
                'value' => $product->sold_count,
            ])
            ->values()
            ->all();
    }

    /**
     * Last 6 months of orders placed on the store, grouped by month.
     * Zero-filled so months with no orders still appear.
     */
    private function getOrdersOverview(Store $store): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $rows = $store->orders()
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');

            $result[] = [
                'month' => $key,
                'label' => $date->format('M Y'),
                'orders' => (int) ($rows[$key] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Order status breakdown for the current month.
     */
    private function getOrderStatusBreakdown(Store $store): array
    {
        $now = Carbon::now();

        $counts = $store->orders()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $palette = [
            OrderStatus::COMPLETED->value => 'var(--chart-2)',
            OrderStatus::DELIVERED->value => 'var(--chart-2)',
            OrderStatus::SHIPPED->value => 'var(--chart-1)',
            OrderStatus::PROCESSING->value => 'var(--chart-3)',
            OrderStatus::PACKED->value => 'var(--chart-4)',
            OrderStatus::CONFIRMED->value => 'var(--chart-1)',
            OrderStatus::PENDING->value => 'var(--chart-4)',
            OrderStatus::CANCELLED->value => 'var(--chart-5)',
            OrderStatus::RETURN_REQUESTED->value => 'var(--chart-5)',
            OrderStatus::RETURN_APPROVED->value => 'var(--chart-5)',
            OrderStatus::RETURNED->value => 'var(--chart-5)',
        ];

        return collect($counts)
            ->filter(fn ($total) => $total > 0)
            ->map(fn ($total, $status) => [
                'key' => $status,
                'label' => OrderStatus::from($status)->label(),
                'value' => (int) $total,
                'color' => $palette[$status] ?? 'var(--chart-1)',
            ])
            ->values()
            ->all();
    }
}