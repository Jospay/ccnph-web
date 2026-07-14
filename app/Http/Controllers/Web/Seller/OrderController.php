<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Http\Resources\Seller\OrderIndexResource;
use App\Http\Resources\Seller\OrderShowResource;
use App\Http\Requests\Seller\OrderActionRequest;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['to-confirm', 'to-pack', 'to-ship', 'cancellation'])],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'to-confirm',
        ];

        $user = $request->user()->loadMissing(['store']);

        if (! $user->store) {
            return redirect()->route('seller.store.create');
        }

        $orders = $this->buildBaseQuery($user->store->id, $filters)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('seller/order/Index', [
            'store' => $user->store,
            'orders' => OrderIndexResource::collection($orders),
            'counts' => $this->getSummaryCounts($user->store->id),
            'filters' => $filters
        ]);
        
    }

    private function buildBaseQuery(int $storeId, array $filters): Builder
    {
        return Order::query()
            ->where('store_id', $storeId)
            ->with(['items'])
            ->when(
                $filters['tab'],
                fn (Builder $query) => $this->applyStatusFilter(
                    $query,
                    $filters['tab']
                )
            );
    }

    private function applyStatusFilter(Builder $query, string $status): Builder 
    {
        return match ($status) {
            'to-confirm' => $query->whereIn('status', [
                OrderStatus::PENDING,
                OrderStatus::CONFIRMED,
            ]),
            'to-pack' => $query->where('status', OrderStatus::PROCESSING),
            'to-ship' => $query->where('status', OrderStatus::PACKED),
            'cancellation' => $query->where('status', OrderStatus::CANCELLED),
            default => $query,
        };
    }

    private function getSummaryCounts(int $storeId): array
    {
        $counts = Order::query()
            ->where('store_id', $storeId)
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->pluck('total', 'status');

        return [
            'to_confirm'   => (int) ($counts->get(OrderStatus::PENDING->value, 0)) 
                            + ($counts->get(OrderStatus::CONFIRMED->value, 0)),
            'to_pack'      =>(int) $counts->get(OrderStatus::PROCESSING->value, 0),
            'to_ship'   => (int) $counts->get(OrderStatus::PACKED->value, 0),
            'cancellation' => (int) $counts->get(OrderStatus::CANCELLED->value, 0),
        ];
    }

    public function show(Request $request, Order $order)
    {
        $order->loadMissing([
            'store',
            'items',
        ]);

        abort_unless(
            $request->user()->id === $order->store->user_id,
            403
        );

        return OrderShowResource::make($order);
    }

    public function action(OrderActionRequest $request, Order $order) {
        $user = $request->user();

        abort_unless(
            $order->store_id === $user->store?->id,
            403
        );

        $order->loadMissing('checkout.payment.method');
        $action = $request->validated('action');
        $payment = $order->checkout?->payment; 
        
        if (!$payment || $payment->payment_method_id !== PaymentMethod::CASH_ON_DELIVERY) {
            return back()->with(
                'error',
                'Online payment workflow is not yet implemented.'
            );
        }

        match ($action) {
            'accept' => $this->acceptCodOrder($order),
            'pack' => $this->packCodOrder($order),
            'ship' => $this->shipCodOrder($order),
        };

        return back()->with(
            'success',
            'Order updated successfully.'
        );
    }

    private function acceptCodOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::PENDING) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::PROCESSING,
            'confirmed_at' => now(),
            'processing_at' => now(),
        ]);
    }

    private function packCodOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::PROCESSING) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::PACKED,
            'packed_at' => now(),
        ]);
    }

    private function shipCodOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::PACKED) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::SHIPPED,
            'shipped_at' => now(),
        ]);
    }

    public function cancel(Request $request, Order $order) {
        $user = $request->user();

        abort_unless(
            $order->store_id === $user->store?->id,
            403
        );

        if ($order->status !== OrderStatus::PENDING) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with(
            'success',
            'Order cancelled successfully.'
        );
    }
}
