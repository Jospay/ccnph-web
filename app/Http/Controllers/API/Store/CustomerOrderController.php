<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\OrderIndexResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ], 401);
        }

        // 1. ADD 'return_requested' and 'return_approved' TO VALIDATION
        $validated = $request->validate([
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    'all',
                    'to-pay',
                    'to-ship',
                    'to-receive',
                    'completed',
                    'cancelled',
                    'return_requested',
                    'return_approved',
                    'returned',
                ]),
            ],
        ]);

        $filters = [
            'status' => $validated['status'] ?? 'all',
        ];

        // Optimized single-query badge counter aggregation
        $badgeCountsRaw = Order::query()
            ->where('user_id', $user->id)
            ->selectRaw('
                COUNT(CASE WHEN status = ? THEN 1 END) as to_pay,
                COUNT(CASE WHEN status IN (?, ?, ?) THEN 1 END) as to_ship,
                COUNT(CASE WHEN status = ? THEN 1 END) as to_receive,
                COUNT(CASE WHEN status = ? THEN 1 END) as to_rate
            ', [
                OrderStatus::PENDING->value ?? OrderStatus::PENDING,
                OrderStatus::CONFIRMED->value ?? OrderStatus::CONFIRMED,
                OrderStatus::PROCESSING->value ?? OrderStatus::PROCESSING,
                OrderStatus::PACKED->value ?? OrderStatus::PACKED,
                OrderStatus::SHIPPED->value ?? OrderStatus::SHIPPED,
                OrderStatus::DELIVERED->value ?? OrderStatus::DELIVERED,
            ])
            ->first();

        $badgeCounts = [
            'to_pay' => (int) ($badgeCountsRaw->to_pay ?? 0),
            'to_ship' => (int) ($badgeCountsRaw->to_ship ?? 0),
            'to_receive' => (int) ($badgeCountsRaw->to_receive ?? 0),
            'to_rate' => (int) ($badgeCountsRaw->to_rate ?? 0),
        ];

        // Fetch records cleanly with relations loaded
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
            'to-receive' => $query->whereIn('status', [
                OrderStatus::SHIPPED,
                OrderStatus::DELIVERED,
            ]),
            'completed' => $query->where('status', OrderStatus::COMPLETED),
            'cancelled' => $query->where('status', OrderStatus::CANCELLED),
            'return_requested' => $query->whereIn('status', [
                OrderStatus::RETURN_REQUESTED,
                OrderStatus::RETURN_APPROVED,
                OrderStatus::RETURNED,
            ]),
            'returned' => $query->whereIn('status', [
                OrderStatus::RETURN_REQUESTED,
                OrderStatus::RETURN_APPROVED,
                OrderStatus::RETURNED,
            ]),
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

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->only('name', 'phone', 'avatar'),
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value ?? $order->status,
                    'raw_status' => $order->status->value ?? $order->status,
                    'status_label' => method_exists($order->status, 'label')
                                        ? $order->status->label()
                                        : str_replace('_', ' ', $order->status->value ?? $order->status),
                    'shipping_fee' => (float) $order->shipping_fee,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
                    'store' => $order->store,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_image' => $item->product_image ? Storage::url($item->product_image) : null,
                        'variant_name' => $item->variant_name,
                        'price' => (float) $item->price,
                        'quantity' => $item->quantity,
                    ])->values()->all(),
                    'shipping_name' => $order->recipient_name,
                    'shipping_phone' => $order->recipient_phone,
                    'shipping_address' => $fullAddress ?: 'No shipping address provided.',

                    'tracking' => [
                        'created_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
                        'confirmed_at' => $order->confirmed_at ? $order->confirmed_at->toIso8601String() : null,
                        'processing_at' => $order->processing_at ? $order->processing_at->toIso8601String() : null,
                        'packed_at' => $order->packed_at ? $order->packed_at->toIso8601String() : null,
                        'shipped_at' => $order->shipped_at ? $order->shipped_at->toIso8601String() : null,
                        'delivered_at' => $order->delivered_at ? $order->delivered_at->toIso8601String() : null,
                        'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->toIso8601String() : null,
                        'returned_at' => $order->returned_at ? $order->returned_at->toIso8601String() : null,
                    ],

                    'confirmed_at' => $order->confirmed_at ? $order->confirmed_at->toIso8601String() : null,
                    'processing_at' => $order->processing_at ? $order->processing_at->toIso8601String() : null,
                    'packed_at' => $order->packed_at ? $order->packed_at->toIso8601String() : null,
                    'shipped_at' => $order->shipped_at ? $order->shipped_at->toIso8601String() : null,
                    'delivered_at' => $order->delivered_at ? $order->delivered_at->toIso8601String() : null,
                    'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->toIso8601String() : null,
                    'returned_at' => $order->returned_at ? $order->returned_at->toIso8601String() : null,
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

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'items.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $order, $request) {
            foreach ($validated['items'] as $itemData) {
                $item = OrderItem::where('id', $itemData['order_item_id'])
                    ->where('order_id', $order->id)
                    ->first();

                if ($item && method_exists($item, 'reviews')) {
                    $item->reviews()->create([
                        'user_id' => $request->user()->id,
                        'rating' => $itemData['rating'],
                        'comment' => $itemData['comment'] ?? null,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    'cancelled',
                    'completed',
                    'delivered',
                    'return_requested',
                    'return_approved',
                    'returned',
                ]),
            ],
        ]);

        $targetStatus = $request->input('status');

        // --- CANCEL ORDER ---
        if ($targetStatus === 'cancelled') {
            if ($order->status !== OrderStatus::PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order can no longer be cancelled.',
                ], 422);
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order successfully cancelled.',
            ]);
        }

        // --- RETURN REQUESTED BY CUSTOMER ---
        if ($targetStatus === 'return_requested') {
            if (! in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only shipped, delivered, or completed orders can request a return.',
                ], 422);
            }

            $order->update([
                'status' => OrderStatus::RETURN_REQUESTED,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully.',
            ]);
        }

        // --- RETURN APPROVED (E.g. by Admin or System) ---
        if ($targetStatus === 'return_approved') {
            if ($order->status !== OrderStatus::RETURN_REQUESTED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only orders with a pending return request can be approved.',
                ], 422);
            }

            $order->update([
                'status' => OrderStatus::RETURN_APPROVED,
                'returned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request has been approved.',
            ]);
        }

        // --- COMPLETED / DELIVERED ---
        if (in_array($targetStatus, ['completed', 'delivered'])) {
            if (! in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only shipped or delivered orders can be marked as completed.',
                ], 422);
            }

            $order->update([
                'status' => OrderStatus::COMPLETED,
                'delivered_at' => $order->delivered_at ?? now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid status transition requested.',
        ], 422);
    }
}
