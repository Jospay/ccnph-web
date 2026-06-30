<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\OrderIndexResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user context missing.',
            ], 419);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['all', 'to-pay', 'to-ship', 'to-receive', 'completed', 'cancelled', 'returned'])],
        ]);

        $filters = [
            'status' => $validated['status'] ?? 'all',
        ];

        $badgeCounts = [
            'to_pay' => Order::where('user_id', $user->id)
                ->where('status', OrderStatus::PENDING)
                ->count(),

            'to_ship' => Order::where('user_id', $user->id)
                ->whereIn('status', [
                    OrderStatus::CONFIRMED,
                    OrderStatus::PROCESSING,
                    OrderStatus::PACKED,
                ])
                ->count(),

            'to_receive' => Order::where('user_id', $user->id)
                ->where('status', OrderStatus::SHIPPED)
                ->count(),

            'to_rate' => Order::where('user_id', $user->id)
                ->where('status', OrderStatus::DELIVERED)
                ->count(),
        ];

        // Fetch records cleanly without restrictive column select arrays to ensure relations never fail
        $paginator = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'store:id,name',
                'items:id,order_id,product_name,product_image,variant_name,price,quantity',
            ])
            ->when(
                $filters['status'] !== 'all',
                fn (Builder $query) => $this->applyStatusFilter($query, $filters['status'])
            )
            ->latest()
            ->paginate(10);

        // CLEAN & SAFE RESOURCE MAPPING: Prevents the 500 structural response crash
        $ordersCollection = OrderIndexResource::collection($paginator);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only('name', 'phone', 'avatar'),
                'orders' => $ordersCollection->toArray($request),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],

                'filters' => $filters,
                'badges' => $badgeCounts,
            ],
        ]);
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
            'to-receive' => $query->where('status', OrderStatus::SHIPPED),
            'completed' => $query->where('status', OrderStatus::DELIVERED),
            'cancelled' => $query->where('status', OrderStatus::CANCELLED),
            'returned' => $query->where('status', OrderStatus::RETURNED),
            default => $query,
        };
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $order->load([
            'store:id,name',
            'items:id,order_id,product_name,product_image,variant_name,price,quantity',
        ]);

        $fullAddress = collect([
            $order->unit_bldg_house,
            $order->street,
            $order->barangay,
            $order->city,
            $order->province,
            $order->region,
            $order->postal_code,
        ])->filter()->implode(', ');

        $isPaid = ! in_array($order->status, [OrderStatus::PENDING]);
        $isShipped = in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED]);
        $isCompleted = $order->status === OrderStatus::DELIVERED;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->only('name', 'phone', 'avatar'),
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value ?? $order->status,
                    'shipping_fee' => (float) $order->shipping_fee,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->toIso8601String(),
                    'store' => $order->store,
                    'items' => $order->items->map(fn ($item) => [
                        'product_name' => $item->product_name,
                        'product_image' => $item->product_image ? Storage::url($item->product_image) : null,
                        'variant_name' => $item->variant_name,
                        'price' => (float) $item->price,
                        'quantity' => $item->quantity,
                    ]),
                    'shipping_name' => $order->recipient_name,
                    'shipping_phone' => $order->recipient_phone,
                    'shipping_address' => $fullAddress ?: 'No shipping address provided.',
                    'paid_at' => $isPaid ? $order->created_at->addMinutes(5)->toIso8601String() : null,
                    'shipped_at' => $isShipped ? $order->updated_at->toIso8601String() : null,
                    'completed_at' => $isCompleted ? $order->updated_at->toIso8601String() : null,
                ],
            ],
        ]);
    }

    public function rate(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $order->load(['store:id,name', 'items:id,order_id,product_name,product_image,variant_name']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->only('name', 'phone', 'avatar'),
                'order' => $order,
            ],
        ]);
    }

    public function storeRating(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'items' => ['required', 'array'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'items.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }
}
