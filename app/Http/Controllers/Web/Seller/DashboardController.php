<?php

namespace App\Http\Controllers\Web\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('shop');

        if (! $user->shop) {
            return redirect()->route('seller.shop.create');
        }
        $shop = $user->shop;

        return Inertia::render('seller/dashboard/Index', [
            'shop' => $shop,
            'productsSummary' => $this->productsSummary($shop),
            'ordersSummary' => $this->ordersSummary($shop),
            'salesSummary' => $this->salesSummary($shop),
        ]);
    }

    private function productsSummary(shop $shop): array
    {
        $total = $shop->products()->count();
        $inactive = $shop->products()->where('is_active', false)->count();

        $activeOutOfStock = $shop->products()
            ->where('is_active', true)
            ->whereHas('variants')
            ->whereDoesntHave('variants', fn ($q) => $q->where('stock', '>', 0))
            ->count();
        $activeInStock = max($total - $inactive - $activeOutOfStock, 0);

        return [
            'total' => $total,
            'totalViews' => (int) $shop->products()->sum('views'),
            'chart' => [
                ['key' => 'in_stock', 'label' => 'Active & In Stock', 'value' => $activeInStock],
                ['key' => 'out_of_stock', 'label' => 'Active & Out of Stock', 'value' => $activeOutOfStock],
                ['key' => 'inactive', 'label' => 'Inactive', 'value' => $inactive],
            ],
        ];
    }

    private function ordersSummary(shop $shop): array
    {
        $counts = $shop->orders()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $toConfirm = ($counts[OrderStatus::PENDING->value] ?? 0)
            + ($counts[OrderStatus::CONFIRMED->value] ?? 0);
        $toPack = $counts[OrderStatus::PROCESSING->value] ?? 0;
        $toShip = $counts[OrderStatus::PACKED->value] ?? 0;
        $cancelled = $counts[OrderStatus::CANCELLED->value] ?? 0;

        return [
            'total' => $toConfirm + $toPack + $toShip + $cancelled,
            'chart' => [
                ['key' => 'to_confirm', 'label' => 'To Confirm', 'value' => $toConfirm],
                ['key' => 'to_pack', 'label' => 'To Pack', 'value' => $toPack],
                ['key' => 'to_ship', 'label' => 'To Ship', 'value' => $toShip],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'value' => $cancelled],
            ],
        ];
    }

    private function salesSummary(shop $shop): array
    {
        $counts = $shop->orders()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $toReceive = $counts[OrderStatus::SHIPPED->value] ?? 0;
        $completed = ($counts[OrderStatus::DELIVERED->value] ?? 0)
            + ($counts[OrderStatus::COMPLETED->value] ?? 0);
        $returnRequest = ($counts[OrderStatus::RETURN_REQUESTED->value] ?? 0)
            + ($counts[OrderStatus::RETURN_APPROVED->value] ?? 0);
        $returned = $counts[OrderStatus::RETURNED->value] ?? 0;

        $totalAmount = (float) $shop->orders()
            ->where('status', OrderStatus::COMPLETED->value)
            ->sum('total');

        return [
            'totalAmount' => $totalAmount,
            'total' => $toReceive + $completed + $returnRequest + $returned,
            'chart' => [
                ['key' => 'to_receive', 'label' => 'To Receive', 'value' => $toReceive],
                ['key' => 'completed', 'label' => 'Completed', 'value' => $completed],
                ['key' => 'return_request', 'label' => 'Return Request', 'value' => $returnRequest],
                ['key' => 'returned', 'label' => 'Returned', 'value' => $returned],
            ],
        ];
    }
}