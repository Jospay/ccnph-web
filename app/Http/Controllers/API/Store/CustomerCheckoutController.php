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
     * Step 1: Select cart items for checkout.
     * Instead of a session, we validate and return the item IDs back to React Native.
     */
    public function select(CheckoutSelectRequest $request): JsonResponse
    {
        $user = $request->user();

        $cartItems = CartItem::query()
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('id', $request->validated('cart_item_ids'))
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
            'cart_item_ids' => $cartItems->values()->all(),
        ]);
    }

    /**
     * Step 2: Fetch checkout screen details.
     * Instead of looking at a session, React Native sends the checked item IDs via request.
     */
    public function index(Request $request): JsonResponse
    {
        // React Native should pass cart_item_ids as query parameters: /checkout?cart_item_ids[]=1&cart_item_ids[]=2
        $cartItemIds = $request->input('cart_item_ids');

        if (empty($cartItemIds) || ! is_array($cartItemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No items provided for checkout.',
            ], 422);
        }

        $user = $request->user();

        $cartItems = CartItem::query()
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('id', $cartItemIds)
            ->with([
                'productVariant.product.store',
                'productVariant.attributeValues.attribute',
            ])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid cart items found.',
            ], 422);
        }

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->orderBy('name')
            ->get();

        $subtotal = $cartItems->sum(
            fn ($item) => $item->quantity * $item->productVariant->price
        );

        // temporary shipping fee logic
        $shippingFee = $cartItems
            ->groupBy(fn ($item) => $item->productVariant->product->store_id)
            ->count() * 60;

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => UserAddressResource::collection($addresses),
                'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
                'items' => CheckoutItemResource::collection($cartItems),
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

        return match ($paymentMethod->slug) {
            'cash-on-delivery' => $this->checkoutCod($request, $paymentMethod),
            'pay-online' => $this->checkoutPayMongo($request, $paymentMethod),
            default => response()->json(['message' => 'Unsupported payment method.'], 422),
        };
    }

    private function getCheckoutData(CheckoutCreateRequest $request): array
    {
        // Expecting React Native to send the cart_item_ids payload inside the POST body
        $cartItemIds = $request->input('cart_item_ids', []);

        if (empty($cartItemIds)) {
            abort(response()->json(['message' => 'No checkout items selected.'], 422));
        }

        $cartItems = $request->user()
            ->cart
            ->items()
            ->whereIn('id', $cartItemIds)
            ->with([
                'productVariant.product.store',
                'productVariant.attributeValues',
            ])
            ->get();

        if ($cartItems->isEmpty()) {
            abort(response()->json(['message' => 'No valid checkout items found.'], 422));
        }

        $address = $request->user()
            ->addresses()
            ->findOrFail($request->address_id);

        return [
            'cartItems' => $cartItems,
            'address' => $address,
        ];
    }

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

        // Delete items from the mobile user's cart database
        $cartItems->each->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully via Cash on Delivery!',
            'order_id' => $order->id,
        ], 201);
    }

    private function checkoutPayMongo(CheckoutCreateRequest $request, PaymentMethod $paymentMethod)
    {
        // Implement your PayMongo logic here returning json responses.
        return response()->json(['message' => 'PayMongo gateway not configured yet.'], 501);
    }
}
