<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CheckoutCreateRequest;
use App\Http\Requests\Store\CheckoutSelectRequest;
use App\Http\Resources\Api\Store\CheckoutItemResource;
use App\Http\Resources\Api\Store\PaymentMethodResource;
use App\Http\Resources\Api\Store\UserAddressResource;
use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Points\PointService;
use App\Services\Store\CheckoutPricingService;
use App\Services\Store\CheckoutService;
use App\Services\Store\StorePayMongoService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerCheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected StorePayMongoService $payMongo,
        protected PointService $pointService,
        protected CheckoutPricingService $pricing,
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

        // Only the methods the store checkout supports
        $paymentMethods = PaymentMethod::query()
            ->whereIn('id', PaymentMethod::STORE_CHECKOUT_METHODS)
            ->orderBy('id')
            ->get();

        // same numbers CheckoutService will use when the order is placed
        $pricing = $this->pricing->preview($user, $itemsCollection);

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => UserAddressResource::collection($addresses),
                'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
                'items' => CheckoutItemResource::collection($itemsCollection),
                // Spendable points (1 point = ₱1)
                'points_balance' => (float) $this->pointService->getBalance($user),
                // total = COD price. Points: total - point_discount. Online: total + online_fee.
                'summary' => $pricing,
            ],
        ]);
    }

    /**
     * Step 3: Place the order.
     */
    public function store(CheckoutCreateRequest $request): JsonResponse
    {
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        return match (true) {
            $paymentMethod->id === PaymentMethod::CASH_ON_DELIVERY => $this->checkoutCod($request, $paymentMethod),
            $paymentMethod->id === PaymentMethod::POINT => $this->checkoutPoints($request, $paymentMethod),
            $paymentMethod->isOnline() => $this->checkoutOnline($request, $paymentMethod),
            default => response()->json(['message' => 'Unsupported payment method.'], 422),
        };
    }

    /**
     * Polled by the app after an online payment was started.
     *
     * Two things can mark a payment as paid:
     *  1. the shared PayMongo webhook (POST /api/webhooks/paymongo). It needs a
     *     public URL, so it never arrives while developing on localhost.
     *  2. this endpoint, which asks PayMongo for the intent directly. This is
     *     what confirms the payment locally, and it is also the safety net in
     *     production if a webhook is late or missed.
     */
    public function paymentStatus(Request $request, Checkout $checkout): JsonResponse
    {
        abort_unless($checkout->user_id === $request->user()->id, 403);

        $payment = $this->checkoutService->paymentFor($checkout);

        abort_if(! $payment, 404, 'Payment not found.');

        $status = 'pending';
        $redirectUrl = null;
        $qrImage = null;

        if ((int) $payment->status_id === CheckoutService::PAYMENT_STATUS_PAID) {
            // webhook may have paid the payment row first: make sure the checkout row is paid too
            $this->checkoutService->markAsPaid($checkout);
            $status = 'paid';
        } elseif ((int) $payment->status_id === CheckoutService::PAYMENT_STATUS_FAILED) {
            $status = 'failed';
        } elseif ($payment->gateway_payment_intent_id) {
            $intent = null;

            // only the PayMongo call is guarded: a failure inside markAsPaid must not be hidden
            try {
                $intent = $this->payMongo->retrieve($payment->gateway_payment_intent_id);
            } catch (Throwable $e) {
                Log::warning('Store payment status: PayMongo retrieve failed.', [
                    'checkout_id' => $checkout->id,
                    'intent_id' => $payment->gateway_payment_intent_id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($intent) {
                if ($intent['status'] === 'succeeded') {
                    $this->confirmPaid($checkout, $payment, $intent['payment_id'] ?? null);
                    $status = 'paid';
                } elseif ($intent['failed']) {
                    $this->checkoutService->markAsFailed($checkout);
                    $status = 'failed';
                } else {
                    $redirectUrl = $intent['redirect_url'];
                    $qrImage = $intent['qr_image'];
                }
            }
        }

        $method = PaymentMethod::find($payment->payment_method_id);

        // payments.fee is stored in cents, grand_total / order columns are in pesos
        $fee = round(((int) ($payment->fee ?? 0)) / 100, 2);
        $orderTotal = (float) $checkout->grand_total;
        $amount = round($orderTotal + $fee, 2);

        $orders = $checkout->orders()->get();

        $breakdown = [
            'subtotal' => round((float) $orders->sum('subtotal'), 2),
            'shipping_fee' => round((float) $orders->sum('shipping_fee'), 2),
            'membership_discount' => round((float) $orders->sum('membership_discount'), 2),
            'shared_capital_discount' => round((float) $orders->sum('shared_capital_discount'), 2),
            'point_discount' => round((float) $orders->sum('point_discount'), 2),
            'order_total' => $orderTotal,
            'fee' => $fee,
            'amount' => $amount,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'method' => $method?->name,
                // what the user actually pays: order total + online processing fee
                'amount' => $amount,
                'fee' => $fee,
                'order_total' => $orderTotal,
                'breakdown' => $breakdown,
                'checkout_number' => $checkout->checkout_number,
                'redirect_url' => $redirectUrl,
                'qr_image' => $qrImage,
            ],
        ]);
    }

    /**
     * User backed out of the payment screen: release the reserved stock, keep the cart.
     *
     * The user may have already paid in their wallet / bank app right before pressing
     * cancel, so PayMongo is checked first. A paid intent is never cancelled.
     */
    public function cancelPayment(Request $request, Checkout $checkout): JsonResponse
    {
        abort_unless($checkout->user_id === $request->user()->id, 403);

        $payment = $this->checkoutService->paymentFor($checkout);

        if ((int) $payment?->status_id === CheckoutService::PAYMENT_STATUS_PAID) {
            return response()->json([
                'success' => false,
                'message' => 'This order is already paid.',
            ], 422);
        }

        if ($payment?->gateway_payment_intent_id) {
            try {
                $intent = $this->payMongo->retrieve($payment->gateway_payment_intent_id);
            } catch (Throwable $e) {
                Log::warning('Store payment cancel: PayMongo retrieve failed.', [
                    'checkout_id' => $checkout->id,
                    'intent_id' => $payment->gateway_payment_intent_id,
                    'error' => $e->getMessage(),
                ]);

                // can't be sure the money wasn't taken: don't release the order blindly
                return response()->json([
                    'success' => false,
                    'message' => 'We could not verify your payment right now. Please try again in a moment.',
                ], 503);
            }

            if ($intent['status'] === 'succeeded') {
                $this->confirmPaid($checkout, $payment, $intent['payment_id'] ?? null);

                return response()->json([
                    'success' => false,
                    'message' => 'This order is already paid.',
                ], 422);
            }
        }

        $this->checkoutService->markAsFailed($checkout);

        return response()->json([
            'success' => true,
            'message' => 'Payment cancelled.',
        ]);
    }

    /**
     * Mark a checkout paid after PayMongo confirmed the intent succeeded.
     * Stores the gateway payment id like the webhook does, then runs the same
     * idempotent markAsPaid used by the webhook and the points flow.
     */
    private function confirmPaid(Checkout $checkout, Payment $payment, ?string $gatewayPaymentId): void
    {
        if ($gatewayPaymentId && ! $payment->gateway_payment_id) {
            $payment->forceFill(['gateway_payment_id' => $gatewayPaymentId])->save();
        }

        $this->checkoutService->markAsPaid($checkout);
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
     * Shared: create checkout + orders and reserve stock.
     * $clearCart = false for online payments: the cart is cleared when the payment succeeds
     * (CheckoutService::markAsPaid reads cart_item_ids from the payment meta).
     * Must be called inside a DB transaction.
     *
     * @return array{0: Checkout, 1: UserAddress}
     */
    private function placeOrder(
        CheckoutCreateRequest $request,
        PaymentMethod $paymentMethod,
        bool $clearCart = true,
    ): array {
        [
            'cartItems' => $cartItems,
            'address' => $address,
        ] = $this->getCheckoutData($request);

        $isCart = $request->input('mode', 'cart') === 'cart';

        $checkout = $this->checkoutService->createCheckout(
            user: $request->user(),
            address: $address,
            paymentMethod: $paymentMethod,
            cartItems: $cartItems,
            note: $request->note,
            meta: (! $clearCart && $isCart)
                ? ['cart_item_ids' => $cartItems->pluck('id')->values()->all()]
                : [],
        );

        foreach ($cartItems as $cartItem) {
            $cartItem->productVariant()
                ->decrement('stock', $cartItem->quantity);
        }

        if ($clearCart && $isCart) {
            $cartItems->each->delete();
        }

        return [$checkout, $address];
    }

    /**
     * Cash on Delivery
     */
    private function checkoutCod(CheckoutCreateRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        [$checkout] = DB::transaction(
            fn () => $this->placeOrder($request, $paymentMethod)
        );

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully via Cash on Delivery!',
            'checkout_id' => $checkout->id,
            'order_id' => $checkout->id,
        ], 201);
    }

    /**
     * Pay with store points (1 point = ₱1).
     */
    private function checkoutPoints(CheckoutCreateRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $user = $request->user();

        $checkout = DB::transaction(function () use ($request, $paymentMethod, $user) {
            [$checkout] = $this->placeOrder($request, $paymentMethod);

            try {
                // spend() locks the point row and re-checks the balance + integrity
                $this->pointService->spend(
                    $user,
                    (float) $checkout->grand_total,
                    'Store purchase '.$checkout->checkout_number,
                    $checkout,
                );
            } catch (DomainException $e) {
                throw ValidationException::withMessages([
                    'payment_method_id' => $e->getMessage(),
                ]);
            }

            $this->checkoutService->markAsPaid($checkout);

            return $checkout;
        });

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully using your points!',
            'checkout_id' => $checkout->id,
            'order_id' => $checkout->id,
        ], 201);
    }

    /**
     * QR Ph / Maya / BillEase / GrabPay through PayMongo.
     * Everything rolls back (order, stock) if PayMongo rejects the request.
     * The order only becomes visible/paid after the payment succeeds.
     */
    private function checkoutOnline(CheckoutCreateRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $returnUrl = $request->input('return_url') ?: url('/api/payment/success');

        try {
            [$checkout, $charge] = DB::transaction(function () use ($request, $paymentMethod, $returnUrl) {
                // cart is kept until the payment succeeds
                [$checkout, $address] = $this->placeOrder($request, $paymentMethod, clearCart: false);

                // Append the checkout id so the app can resume the status screen
                $separator = str_contains($returnUrl, '?') ? '&' : '?';
                $finalReturnUrl = $returnUrl.$separator.'checkoutId='.$checkout->id;

                $charge = $this->payMongo->charge(
                    $checkout,
                    $paymentMethod,
                    $request->user(),
                    $address,
                    $finalReturnUrl,
                );

                $checkout->payment->forceFill([
                    'gateway' => PaymentGatewayFactory::resolveGateway($paymentMethod),
                    'gateway_payment_intent_id' => $charge['id'],
                ])->save();

                return [$checkout, $charge];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not start the payment. Please try again or choose another method.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created. Complete your payment to confirm it.',
            'checkout_id' => $checkout->id,
            'payment' => [
                'status' => 'pending',
                'gateway_type' => $paymentMethod->gateway_type,
                'redirect_url' => $charge['redirect_url'],
                'qr_image' => $charge['qr_image'],
            ],
        ], 201);
    }
}
