<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\OrderIndexResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderStatus;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['all', 'to-pay', 'to-ship', 'to-receive', 'completed', 'cancelled', 'returned'])],
        ]);

        $filters = [
            'status' => $validated['status'] ?? 'all',
        ];

        return Inertia::render('shop/customer/order/Index', [
            'user' => $user->only('name', 'phone', 'avatar'),
            'orders' => OrderIndexResource::collection(
                $this->buildBaseQuery($user->id, $filters)
                ->latest()
                ->paginate(10)
                ->withQueryString()
            ),
            'filters' => $filters,
        ]);
    }

    private function buildBaseQuery(int $userId, array $filters): Builder
    {
        return Order::query()
            ->select([
                'id',
                'store_id',
                'status',
                'shipping_fee',
                'total',
                'created_at',
            ])
            ->where('user_id', $userId)
            ->with([
                'store:id,name',
                'items:id,order_id,product_name,product_image,variant_name,price,quantity',
            ])
            ->when(
                $filters['status'] !== 'all',
                fn (Builder $query) => $this->applyStatusFilter(
                    $query,
                    $filters['status']
                )
            );
    }

    private function applyStatusFilter(Builder $query, string $status): Builder 
    {
        return match ($status) {
            'to-pay' => $query->where('status', OrderStatus::PENDING),
            'to-ship' => $query->whereIn('status', [
                OrderStatus::CONFIRMED,
                OrderStatus::PROCESSING,
                OrderStatus::PACKED,
            ]),
            'to-receive' => $query->where(
                'status',
                OrderStatus::SHIPPED
            ),
            'completed' => $query->where(
                'status',
                OrderStatus::DELIVERED
            ),
            'cancelled' => $query->where(
                'status',
                OrderStatus::CANCELLED
            ),
            'returned' => $query->where(
                'status',
                OrderStatus::RETURNED
            ),
            default => $query,
        };
    }

   public function show(Request $request, Order $order)
{
    // Ensure the customer owns this order record
    if ($order->user_id !== $request->user()->id) {
        abort(403, 'Unauthorized action.');
    }

    // 1. Load relationships
    $order->load([
        'store:id,name',
        'items:id,order_id,product_name,product_image,variant_name,price,quantity',
    ]);

    // 2. Format a comprehensive address string out of your granular database columns
    $fullAddress = collect([
        $order->unit_bldg_house,
        $order->street,
        $order->barangay,
        $order->city,
        $order->province,
        $order->region,
        $order->postal_code,
    ])->filter()->implode(', ');

    $isPaid = !in_array($order->status, [OrderStatus::PENDING]);
    $isShipped = in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED]);
    $isCompleted = $order->status === OrderStatus::DELIVERED;

    return Inertia::render('shop/customer/order/Show', [
        'user' => $request->user()->only('name', 'phone', 'avatar'),
        'order' => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value ?? $order->status,
            'shipping_fee' => (float) $order->shipping_fee,
            'total' => (float) $order->total,
            'created_at' => $order->created_at->toIso8601String(),
            'store' => $order->store,
            'items' => $order->items,
            
            'shipping_name' => $order->recipient_name,
            'shipping_phone' => $order->recipient_phone,
            'shipping_address' => $fullAddress ?: 'No shipping address provided.',
            
            // Timeline dates
            'paid_at' => $isPaid ? $order->created_at->addMinutes(5)->toIso8601String() : null,
            'shipped_at' => $isShipped ? $order->updated_at->toIso8601String() : null,
            'completed_at' => $isCompleted ? $order->updated_at->toIso8601String() : null,
        ],
    ]);
    
}
public function rate(Request $request, Order $order)
    {
        // Ensure the customer owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['store:id,name', 'items:id,order_id,product_name,product_image,variant_name']);

        return Inertia::render('shop/customer/order/Rate', [
            'user' => $request->user()->only('name', 'phone', 'avatar'),
            'order' => $order,
        ]);
    }

    public function storeRating(Request $request, Order $order)
    {
        // Ensure the customer owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'items.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Save records to your reviews table across each specific item sequence
        foreach ($validated['items'] as $itemReview) {
            // Example logic model tracking generation:
            // ProductReview::create([...$itemReview, 'user_id' => $request->user()->id]);
        }

        return redirect()->route('shop.orders.index')
            ->with('success', 'Thank you for your feedback!');
    }
}
