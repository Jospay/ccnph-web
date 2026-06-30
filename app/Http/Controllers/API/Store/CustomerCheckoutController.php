<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CheckoutCreateRequest;
use App\Http\Requests\Store\CheckoutSelectRequest;
use App\Http\Resources\Api\Store\CheckoutItemResource;
use App\Http\Resources\Api\Store\PaymentMethodResource;
use App\Http\Resources\Api\Store\UserAddressResource;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Services\Store\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        // protected PayMongoService $payMongoService,
    ) {}

    /**
     * Step 1: Select items for checkout / Verify stock availability.
     * Flexibly accepts either cart_item_ids or direct product_variant_id inputs.
     */
    public function select(CheckoutSelectRequest $request): JsonResponse
    {
        $user = $request->user();
        $mode = $request->input('mode', 'cart');

        if ($mode === 'direct') {
            $variant = ProductVariant::findOrFail($request->input('product_variant_id'));

            if ($variant->stock < $request->integer('quantity')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient product variant stock available.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Direct checkout verified successfully.',
                'payload' => [
                    'mode' => 'direct',
                    'product_variant_id' => $variant->id,
                    'quantity' => $request->integer('quantity'),
                ],
            ]);
        }

        // Standard Cart Flow Logic
        $cartItems = CartItem::query()
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('id', $request->input('cart_item_ids', []))
            ->pluck('id');

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid cart items selected.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items verified successfully.',
            'payload' => [
                'mode' => 'cart',
                'cart_item_ids' => $cartItems->values()->all(),
            ],
        ]);
    }

    /**
     * Step 2: Fetch checkout screen details.
     * Compiles addresses, methods, and calculations contextually without cluttering DB state.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $mode = $request->input('mode', 'cart');
        $itemsCollection = collect();

        if ($mode === 'direct') {
            $variantId = $request->input('product_variant_id');
            $quantity = $request->integer('quantity', 1);

            $variant = ProductVariant::with([
                'product.store',
                'product.images',
                'attributeValues.attribute',
            ])->findOrFail($variantId);

            // Instantiate transient (non-saved) model frame mirroring resource definitions smoothly
            $temporaryCartItem = new CartItem([
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
            $temporaryCartItem->setRelation('productVariant', $variant);

            $itemsCollection->push($temporaryCartItem);
        } else {
            $cartItemIds = $request->input('cart_item_ids');

            if (empty($cartItemIds) || ! is_array($cartItemIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items provided for checkout.',
                ], 422);
            }

            $itemsCollection = CartItem::query()
                ->whereHas('cart', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereIn('id', $cartItemIds)
                ->with([
                    'productVariant.product.store',
                    'productVariant.product.images',
                    'productVariant.attributeValues.attribute',
                ])
                ->get();
        }

        if ($itemsCollection->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid checkout items found.',
            ], 422);
        }

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->orderBy('name')
            ->get();

        $subtotal = $itemsCollection->sum(
            fn ($item) => $item->quantity * $item->productVariant->price
        );

        $shippingFee = $itemsCollection
            ->groupBy(fn ($item) => $item->productVariant->product->store_id)
            ->count() * 60;

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => UserAddressResource::collection($addresses),
                'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
                'items' => CheckoutItemResource::collection($itemsCollection),
                'summary' => [
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount' => 0,
                    'total' => $subtotal + $shippingFee,
                ],
            ],
        ]);
    }

    /**
     * Step 3: Place the order.
     */
    public function store(CheckoutCreateRequest $request): JsonResponse
    {
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        return match ($paymentMethod->id) {
            PaymentMethod::CASH_ON_DELIVERY => $this->checkoutCod($request, $paymentMethod),
            PaymentMethod::PAY_ONLINE => $this->checkoutPayMongo($request, $paymentMethod),
            default => response()->json(['message' => 'Unsupported payment method.'], 422),
        };
    }

    /**
     * Utility: Consolidated abstraction isolating collection assembly safely
     */
    private function getCheckoutData(CheckoutCreateRequest $request): array
    {
        $mode = $request->input('mode', 'cart');
        $itemsCollection = collect();

        if ($mode === 'direct') {
            $variant = ProductVariant::with([
                'product.store',
                'attributeValues',
            ])->findOrFail($request->input('product_variant_id'));

            $temporaryCartItem = new CartItem([
                'product_variant_id' => $variant->id,
                'quantity' => $request->integer('quantity'),
            ]);
            $temporaryCartItem->setRelation('productVariant', $variant);

            $itemsCollection->push($temporaryCartItem);
        } else {
            $cartItemIds = $request->input('cart_item_ids', []);

            if (empty($cartItemIds)) {
                abort(response()->json(['message' => 'No checkout items selected.'], 422));
            }

            $itemsCollection = $request->user()
                ->cart
                ->items()
                ->whereIn('id', $cartItemIds)
                ->with([
                    'productVariant.product.store',
                    'productVariant.attributeValues',
                ])
                ->get();
        }

        if ($itemsCollection->isEmpty()) {
            abort(response()->json(['message' => 'No valid checkout items found.'], 422));
        }

        $address = $request->user()
            ->addresses()
            ->findOrFail($request->address_id);

        return [
            'cartItems' => $itemsCollection,
            'address' => $address,
        ];
    }

    /**
     * Handle order processing for Cash on Delivery purchases
     */
    private function checkoutCod(CheckoutCreateRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        [
            'cartItems' => $cartItems,
            'address' => $address,
        ] = $this->getCheckoutData($request);

        $order = $this->checkoutService
            ->createCheckout(
                user: $request->user(),
                address: $address,
                paymentMethod: $paymentMethod,
                cartItems: $cartItems,
                note: $request->note,
                decrementStock: true
            );

        foreach ($cartItems as $cartItem) {
            $cartItem->productVariant()
                ->decrement('stock', $cartItem->quantity);
        }

        // Only clean out structural data from the persistent table database if using standard cart matching
        if ($request->input('mode', 'cart') === 'cart') {
            $cartItems->each->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully via Cash on Delivery!',
            'order_id' => $order->id,
        ], 201);
    }

    /**
     * Handle payment integration setups (e.g. PayMongo)
     */
    private function checkoutPayMongo(CheckoutCreateRequest $request, PaymentMethod $paymentMethod)
    {
        // Implement your PayMongo logic here returning json responses.
        return response()->json(['message' => 'PayMongo gateway not configured yet.'], 501);
    }
}
