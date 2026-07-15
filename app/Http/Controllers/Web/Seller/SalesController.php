<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Http\Resources\Seller\OrderIndexResource;
use App\Http\Resources\Seller\OrderShowResource;
use App\Http\Requests\Seller\SalesActionRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['to-receive', 'completed', 'return-request', 'returned'])],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'to-receive',
        ];

        $user = $request->user()->loadMissing(['store']);

        if (! $user->store) {
            return redirect()->route('seller.store.create');
        }

        $orders = $this->buildBaseQuery($user->store->id, $filters)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('seller/sales/Index', [
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
            'to-receive' => $query->where('status', OrderStatus::SHIPPED),
            'completed' => $query->whereIn('status', [
                OrderStatus::DELIVERED,
                OrderStatus::COMPLETED
            ]),
            'return-request' => $query->whereIn('status', [
                OrderStatus::RETURN_REQUESTED,
                OrderStatus::RETURN_APPROVED,
            ]),
            'returned' => $query->where('status', OrderStatus::RETURNED),
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
            'to_receive'   => (int) $counts->get(OrderStatus::SHIPPED->value, 0),
            'completed'      =>(int) ($counts->get(OrderStatus::DELIVERED->value, 0)
                            + $counts->get(OrderStatus::COMPLETED->value, 0)),
            'return_request'   => (int) ($counts->get(OrderStatus::RETURN_REQUESTED->value, 0)) 
                            + ($counts->get(OrderStatus::RETURN_APPROVED->value, 0)),
            'returned'      =>(int) $counts->get(OrderStatus::RETURNED->value, 0),
        ];
    }

    public function show(Request $request, Order $order)
    {
        $order->loadMissing([
            'store',
            'items',
            'return',
        ]);

        abort_unless(
            $request->user()->id === $order->store->user_id,
            403
        );

        return OrderShowResource::make($order);
    }


    public function action(SalesActionRequest $request, Order $order) {
        $user = $request->user();

        abort_unless(
            $order->store_id === $user->store?->id,
            403
        );
        $action = $request->validated('action');

        match ($action) {
            'deliver' => $this->deliverOrder($order),
            'accept_return' => $this->acceptReturnOrder($order),
            'decline_return' => $this->declineReturnOrder($order, $request->validated('rejection_reason')),
        };

        return back()->with(
            'success',
            'Order updated successfully.'
        );
    }

    private function deliverOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::SHIPPED) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    private function acceptReturnOrder(Order $order): void
    {
        $order->loadMissing('return');
        if ($order->status !== OrderStatus::RETURN_REQUESTED || !$order->return) {
            abort(422, 'Invalid order state');
        }

        $order->update([
            'status' => OrderStatus::RETURN_APPROVED,
            'return_approved_at' => now(),
        ]);
    }

    private function declineReturnOrder(Order $order, string $rejectionReason): void
    {
        $order->loadMissing('return');
        if ($order->status !== OrderStatus::RETURN_REQUESTED || !$order->return) {
            abort(422, 'Invalid order state');
        }

        $order->return->update([
            'rejection_reason' => $rejectionReason,
        ]);


        $order->update([
            'status' => OrderStatus::DELIVERED,
            'return_approved_at' => null,
        ]);
    }
}
