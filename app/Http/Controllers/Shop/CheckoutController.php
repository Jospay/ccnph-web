<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutSelectRequest;
use App\Http\Requests\Shop\CheckoutCreateRequest;
use App\Http\Resources\Shop\CheckoutItemResource;
use App\Http\Resources\Shop\PaymentMethodResource;
use App\Http\Resources\Shop\UserAddressResource;
use App\Services\CheckoutService;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        // protected PayMongoService $payMongoService,
    ) {}

    public function select(CheckoutSelectRequest $request) {
        $user = $request->user();

        $cartItems = CartItem::query()
            ->whereHas('cart', function ($query) use ($user) {
                $query->where(
                    'user_id',
                    $user->id
                );
            })
            ->whereIn(
                'id',
                $request->validated('cart_item_ids')
            )
            ->pluck('id');

        if ($cartItems->isEmpty()) {
            return back()->with(
                'error',
                'No valid cart items selected.'
            );
        }

        session([
            'checkout' => [
                'type' => 'cart',
                'cart_item_ids' => $cartItems->values()->all(),
            ]
        ]);

        return redirect()->route('shop.checkout.index');
    }

    public function index(Request $request)
    {
        $checkout = session('checkout');

        if (! $checkout) {
            return redirect()
                ->route('shop.cart.index');
        }

        $user = $request->user();

        $cartItems = CartItem::query()
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn(
                'id',
                $checkout['cart_item_ids']
            )
            ->with([
                'productVariant.product.store',
                'productVariant.attributeValues.attribute',
            ])
            ->get();

        if ($cartItems->isEmpty()) {
            session()->forget('checkout');

            return redirect()
                ->route('shop.cart.index');
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

        // temporary
        $shippingFee = $cartItems
            ->groupBy(
                fn ($item) => $item->productVariant->product->store_id
            )
            ->count() * 60;

        return Inertia::render(
            'shop/customer/checkout/Index',
            [
                'addresses' => UserAddressResource::collection($addresses)->resolve(),
                'paymentMethods' => PaymentMethodResource::collection($paymentMethods)->resolve(),
                'items' => CheckoutItemResource::collection($cartItems)->resolve(),
                'summary' => [
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount' => 0,
                    'total' => $subtotal + $shippingFee,
                ],
            ]
        );
    }

    public function store(CheckoutCreateRequest $request)
    {
        $paymentMethod = PaymentMethod::findOrFail(
            $request->payment_method_id
        );

        return match ($paymentMethod->slug) {
            'cash-on-delivery'
                => $this->checkoutCod(
                    $request,
                    $paymentMethod
                ),

            'pay-online'
                => $this->checkoutPayMongo(
                    $request,
                    $paymentMethod
                ),

            default => abort(422),
        };
    }

    private function getCheckoutData(CheckoutCreateRequest $request): array {
        $checkout = session('checkout');
        $cartItemIds = ($checkout && ($checkout['type'] ?? null) === 'cart') 
            ? ($checkout['cart_item_ids'] ?? []) 
            : [];

        if (empty($cartItemIds)) {
            abort(422, 'No checkout items selected.');
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
            abort(422, 'No valid checkout items.');
        }

        $address = $request->user()
            ->addresses()
            ->findOrFail(
                $request->address_id
            );

        return [
            'cartItems' => $cartItems,
            'address' => $address,
        ];
    }

    private function checkoutCod(CheckoutCreateRequest $request, PaymentMethod $paymentMethod) {
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
                ->decrement(
                    'stock',
                    $cartItem->quantity
                );
        }

        $cartItems->each->delete();

        session()->forget('checkout');

        return redirect()
            ->route('shop.orders.index')
            ->with('success', 'Order placed successfully via Cash on Delivery!');
    }

    private function checkoutPayMongo(CheckoutCreateRequest $request, PaymentMethod $paymentMethod) {
        abort(403, 'online payment is not available yet.');
        // [
        //     'cartItems' => $cartItems,
        //     'address' => $address,
        // ] = $this->getCheckoutData($request);

        // $order = $this->checkoutService
        //     ->createCheckout(
        //         user: $request->user(),
        //         address: $address,
        //         paymentMethod: $paymentMethod,
        //         cartItems: $cartItems,
        //         note: $request->note,
        //     );

        // $checkoutUrl = $this->payMongoService
        //     ->createCheckoutSession(
        //         order: $order,
        //         cartItems: $cartItems
        //     );

        // return inertia_location(
        //     $checkoutUrl
        // );
    }
}