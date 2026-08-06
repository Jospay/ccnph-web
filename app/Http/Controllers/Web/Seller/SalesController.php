<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Http\Resources\Seller\OrderIndexResource;
use App\Http\Resources\Seller\OrderShowResource;
use App\Http\Requests\Seller\Sales\ActionSalesRequest;
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

        $user = $request->user()->loadMissing(['shop']);

        if (! $user->shop) {
            return redirect()->route('seller.shop.create');
        }

        $orders = $this->buildBaseQuery($user->shop->id, $filters)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('seller/sales/Index', [
            'shop' => $user->shop,
            'orders' => OrderIndexResource::collection($orders),
            'counts' => $this->getSummaryCounts($user->shop->id),
            'filters' => $filters
        ]);
        
    }

    private function buildBaseQuery(int $shopId, array $filters): Builder
    {
        return Order::query()
            ->where('shop_id', $shopId)
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
            'return-request' => $query->where('status', OrderStatus::RETURN_REQUESTED),
            'returned' => $query->whereIn('status',  [
                OrderStatus::RETURN_APPROVED,
                OrderStatus::RETURNED,
            ]),
            default => $query,
        };
    }

    private function getSummaryCounts(int $shopId): array
    {
        $counts = Order::query()
            ->where('shop_id', $shopId)
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->pluck('total', 'status');

        return [
            'to_receive'   => (int) $counts->get(OrderStatus::SHIPPED->value, 0),
            'completed'      =>(int) ($counts->get(OrderStatus::DELIVERED->value, 0)
                            + $counts->get(OrderStatus::COMPLETED->value, 0)),
            'return_request'   => (int) $counts->get(OrderStatus::RETURN_REQUESTED->value, 0),
            'returned'      =>(int) ($counts->get(OrderStatus::RETURN_APPROVED->value, 0) 
                            + $counts->get(OrderStatus::RETURNED->value, 0)),
        ];
    }

    public function show(Request $request, Order $order)
    {
        $order->loadMissing([
            'shop',
            'items.orderReturn.images',
        ]);

        abort_unless(
            $request->user()->id === $order->shop->user_id,
            403
        );

        return OrderShowResource::make($order);
    }


    public function action(ActionSalesRequest $request, Order $order) {
        $action = $request->validated('action');

        match ($action) {
            'deliver' => $this->deliverOrder($order),
            'accept_return' => $this->acceptReturnOrder($order),
            'decline_return' => $this->declineReturnOrder($order, $request->validated('rejection_reason')),
            'confirm_return' => $this->confirmReturnOrder($order),
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
        abort_unless(
            $order->status === OrderStatus::RETURN_REQUESTED
                && $order->returns()->exists(),
            422,
            'Invalid order state'
        );

        $order->update([
            'status' => OrderStatus::RETURN_APPROVED,
            'return_approved_at' => now(),
        ]);
    }

    private function declineReturnOrder(Order $order, string $rejectionReason): void
    {
        abort_unless(
            ($order->status === OrderStatus::RETURN_REQUESTED || $order->status === OrderStatus::RETURN_APPROVED)
                && $order->returns()->exists(),
            422,
            'Invalid order state'
        );

        // applies to every item-level return under this order —
        // decline is an order-wide decision even though returns are stored per item
        $order->returns()->update([
            'rejection_reason' => $rejectionReason,
        ]);

        $order->update([
            'status' => OrderStatus::COMPLETED,
            'completed_at' => now(),
            'return_approved_at' => null,
        ]);
    }

    private function confirmReturnOrder(Order $order): void
    {
        abort_unless(
            $order->status === OrderStatus::RETURN_APPROVED
                && $order->returns()->exists(),
            422,
            'Invalid order state'
        );

        $order->update([
            'status' => OrderStatus::RETURNED,
            'returned_at' => now(),
        ]);
    }
}