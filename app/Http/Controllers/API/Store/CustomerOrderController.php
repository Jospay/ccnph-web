<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\OrderIndexResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $completedStatus = OrderStatus::COMPLETED->value ?? 'completed';

        // 1. Calculate Badges
        // Count orders in 'completed' status that STILL HAVE at least one item WITHOUT a review
        $toRateCount = Order::query()
            ->where('user_id', $user->id)
            ->where('status', $completedStatus)
            ->whereHas('items', function ($query) {
                $query->whereDoesntHave('review');
            })
            ->count();

        // Raw query for other simple badge counts
        $badgeCountsRaw = Order::query()
            ->where('user_id', $user->id)
            ->selectRaw('
            COUNT(CASE WHEN status = ? THEN 1 END) as to_pay,
            COUNT(CASE WHEN status IN (?, ?, ?) THEN 1 END) as to_ship,
            COUNT(CASE WHEN status IN (?, ?, ?) THEN 1 END) as to_receive
        ', [
                OrderStatus::PENDING->value ?? 'pending',
                OrderStatus::CONFIRMED->value ?? 'confirmed',
                OrderStatus::PROCESSING->value ?? 'processing',
                OrderStatus::PACKED->value ?? 'packed',
                OrderStatus::SHIPPED->value ?? 'shipped',
                OrderStatus::DELIVERED->value ?? 'delivered',
                'to-receive',
            ])
            ->first();

        $badgeCounts = [
            'to_pay' => (int) ($badgeCountsRaw->to_pay ?? 0),
            'to_ship' => (int) ($badgeCountsRaw->to_ship ?? 0),
            'to_receive' => (int) ($badgeCountsRaw->to_receive ?? 0),
            'to_rate' => $toRateCount, // 👈 Accurately excludes already-rated orders
        ];

        // 2. Fetch paginated orders with relationships
        $paginator = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'store:id,name',
                'items:id,order_id,product_name,product_image,variant_name,price,quantity',
                'items.review:id,order_item_id',
            ])
            ->when(
                $filters['status'] !== 'all',
                fn (Builder $query) => $this->applyStatusFilter($query, $filters['status'])
            )
            ->latest()
            ->paginate(10);

        // 3. Compute `is_rated` boolean on each order
        $paginator->getCollection()->transform(function (Order $order) {
            // Determine if the order status equals OrderStatus::COMPLETED
            $isCompleted = ($order->status instanceof OrderStatus)
                ? $order->status === OrderStatus::COMPLETED
                : $order->status === OrderStatus::COMPLETED->value;

            $order->is_rated = $isCompleted
                && $order->items->isNotEmpty()
                && $order->items->every(fn ($item) => $item->review !== null);

            return $order;
        });

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
                        // 'product_image' => $item->product_image ? Storage::url($item->product_image) : null,
                        'product_image' => $item->product_image ? asset('storage'.$item->product_image) : null,
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
                        'return_requested_at' => $order->return_requested_at ? $order->return_requested_at->toIso8601String() : null,
                        'return_approved_at' => $order->return_approved_at ? $order->return_approved_at->toIso8601String() : null,
                        'returned_at' => $order->returned_at ? $order->returned_at->toIso8601String() : null,
                    ],

                    'confirmed_at' => $order->confirmed_at ? $order->confirmed_at->toIso8601String() : null,
                    'processing_at' => $order->processing_at ? $order->processing_at->toIso8601String() : null,
                    'packed_at' => $order->packed_at ? $order->packed_at->toIso8601String() : null,
                    'shipped_at' => $order->shipped_at ? $order->shipped_at->toIso8601String() : null,
                    'delivered_at' => $order->delivered_at ? $order->delivered_at->toIso8601String() : null,
                    'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->toIso8601String() : null,
                    'return_requested_at' => $order->return_requested_at ? $order->return_requested_at->toIso8601String() : null,
                    'return_approved_at' => $order->return_approved_at ? $order->return_approved_at->toIso8601String() : null,
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

        // Load store, items, and their associated reviews + images
        $order->load([
            'store:id,name',
            'items:id,order_id,product_name,product_image,variant_name',
            'items.review.images',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->only('name', 'phone', 'avatar'),
                'order' => [
                    'id' => $order->id,
                    'store' => $order->store,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'order_id' => $item->order_id,
                        'product_name' => $item->product_name,
                        'product_image' => $item->product_image ? asset('storage/'.$item->product_image) : null,
                        'variant_name' => $item->variant_name,
                        // Pass the review data if it exists
                        'review' => $item->review ? [
                            'id' => $item->review->id,
                            'rating' => (int) $item->review->rating,
                            'comment' => $item->review->comment,
                            'video_url' => $item->review->video ? asset('storage/'.$item->review->video) : null,
                            'is_anonymous' => (bool) $item->review->is_anonymous,
                            'images' => $item->review->images->map(
                                fn ($img) => asset('storage/'.$img->image)
                            )->values()->all(),
                        ] : null,
                    ])->values()->all(),
                ],
            ],
        ]);
    }

    // public function storeRating(Request $request, Order $order)
    // {
    //     if ($order->user_id !== $request->user()->id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized action.',
    //         ], 403);
    //     }

    //     $validated = $request->validate([
    //         'items' => ['required', 'array', 'min:1'],
    //         'items.*.order_item_id' => ['required', 'exists:order_items,id'],
    //         'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
    //         'items.*.comment' => ['nullable', 'string', 'max:1000'],
    //         'items.*.is_anonymous' => ['nullable', 'boolean'],
    //         'items.*.video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:20480'],
    //         'items.*.images' => ['nullable', 'array', 'max:5'],
    //         'items.*.images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
    //     ]);

    //     // Pre-check: don't let the same order_item get rated twice
    //     $orderItemIds = collect($validated['items'])->pluck('order_item_id');
    //     $alreadyRatedIds = Review::where('user_id', $request->user()->id)
    //         ->whereIn('order_item_id', $orderItemIds)
    //         ->pluck('order_item_id')
    //         ->toArray();

    //     if (! empty($alreadyRatedIds)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'One or more items in this order have already been rated.',
    //             'errors' => [
    //                 'items' => ['You already submitted a review for this order.'],
    //             ],
    //         ], 422);
    //     }

    //     try {
    //         DB::transaction(function () use ($validated, $order, $request) {
    //             foreach ($validated['items'] as $index => $itemData) {
    //                 $item = OrderItem::where('id', $itemData['order_item_id'])
    //                     ->where('order_id', $order->id)
    //                     ->first();

    //                 if (! $item) {
    //                     continue;
    //                 }

    //                 $videoPath = null;
    //                 if ($request->hasFile("items.{$index}.video")) {
    //                     $videoPath = $request->file("items.{$index}.video")->store('feedback_products/videos', 'public');
    //                 }

    //                 // updateOrCreate as a safety net against race conditions / double taps
    //                 $review = $item->review()->updateOrCreate(
    //                     ['order_item_id' => $item->id],
    //                     [
    //                         'user_id' => $request->user()->id,
    //                         'shop_id' => $order->shop_id,
    //                         'product_id' => $item->product_id ?? null,
    //                         'rating' => $itemData['rating'],
    //                         'review' => $itemData['comment'] ?? null,
    //                         'video' => $videoPath,
    //                         'is_anonymous' => $itemData['is_anonymous'] ?? false,
    //                     ]
    //                 );

    //                 if ($request->hasFile("items.{$index}.images")) {
    //                     foreach ($request->file("items.{$index}.images") as $imageFile) {
    //                         $imagePath = $imageFile->store('feedback_products/images', 'public');
    //                         $review->images()->create([
    //                             'image' => $imagePath,
    //                         ]);
    //                     }
    //                 }
    //             }
    //         });
    //     } catch (QueryException $e) {
    //         if ((string) $e->getCode() === '23000') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'You have already submitted a review for one of these items.',
    //             ], 422);
    //         }
    //         throw $e;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Thank you for your feedback!',
    //     ]);
    // }

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
            'items.*.is_anonymous' => ['nullable', 'boolean'],
            'items.*.video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:20480'],
            'items.*.images' => ['nullable', 'array', 'max:5'],
            'items.*.images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        // Prevent duplicate reviews
        $orderItemIds = collect($validated['items'])->pluck('order_item_id');

        $alreadyRatedIds = Review::where('user_id', $request->user()->id)
            ->whereIn('order_item_id', $orderItemIds)
            ->pluck('order_item_id')
            ->toArray();

        if (! empty($alreadyRatedIds)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more items in this order have already been rated.',
                'errors' => [
                    'items' => ['You already submitted a review for this order.'],
                ],
            ], 422);
        }

        try {
            DB::transaction(function () use ($validated, $order, $request) {

                $productIds = [];

                foreach ($validated['items'] as $index => $itemData) {

                    $item = OrderItem::where('id', $itemData['order_item_id'])
                        ->where('order_id', $order->id)
                        ->first();

                    if (! $item || ! $item->product_id) {
                        continue;
                    }

                    $productIds[] = $item->product_id;

                    $videoPath = null;

                    if ($request->hasFile("items.{$index}.video")) {
                        $videoPath = $request->file("items.{$index}.video")
                            ->store('feedback_products/videos', 'public');
                    }

                    $review = $item->review()->updateOrCreate(
                        [
                            'order_item_id' => $item->id,
                        ],
                        [
                            'user_id' => $request->user()->id,
                            'shop_id' => $order->shop_id,
                            'product_id' => $item->product_id,
                            'rating' => $itemData['rating'],
                            'comment' => $itemData['comment'] ?? null,
                            'video' => $videoPath,
                            'is_anonymous' => $itemData['is_anonymous'] ?? false,
                        ]
                    );

                    if ($request->hasFile("items.{$index}.images")) {
                        foreach ($request->file("items.{$index}.images") as $imageFile) {
                            $imagePath = $imageFile->store('feedback_products/images', 'public');

                            $review->images()->create([
                                'image' => $imagePath,
                            ]);
                        }
                    }
                }

                /**
                 * Update all affected product ratings.
                 */
                foreach (array_unique($productIds) as $productId) {

                    $stats = Review::where('product_id', $productId)
                        ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
                        ->first();

                    Product::where('id', $productId)->update([
                        'rating' => round((float) ($stats->average_rating ?? 0), 1),
                        'reviews_count' => (int) ($stats->total_reviews ?? 0),
                    ]);
                }

                /**
                 * Update shop rating.
                 */
                if ($shop = Shop::find($order->shop_id)) {
                    $shop->updateRating();
                }
            });
        } catch (QueryException $e) {

            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted a review for one of these items.',
                ], 422);
            }

            throw $e;
        }

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

        $validated = $request->validate([
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
            'cancellation_reason' => [
                'required_if:status,cancelled',
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $targetStatus = $validated['status'];

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
                'cancellation_reason' => $validated['cancellation_reason'],
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
                'return_requested_at' => now(),
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

            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => OrderStatus::COMPLETED,
                    'delivered_at' => $order->delivered_at ?? now(),
                ]);

                $order->loadMissing('items');

                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)
                            ->increment('sold_count', $item->quantity ?? 1);
                    }
                }
            });

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
