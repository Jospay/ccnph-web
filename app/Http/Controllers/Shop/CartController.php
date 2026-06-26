<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\CartResource;
use App\Http\Requests\Shop\CartItemCreateRequest;
use App\Http\Requests\Shop\CartItemUpdateRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductVariant;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart()->firstOrCreate();
        $cart->loadMissing([
            'items.productVariant.product.store',
            'items.productVariant.product.images',
            'items.productVariant.attributeValues.attribute'
        ]);

        return Inertia::render('shop/customer/cart/Index', [
            'cart' => new CartResource($cart)
        ]);
    }

    public function store(CartItemCreateRequest $request)
    {
        $user = $request->user();

        $variant = ProductVariant::query()
            ->with('product')
            ->findOrFail(
                $request->integer('product_variant_id')
            );

        if (! $variant->product->is_active) {
            return back()->with('error', 'Product is unavailable.');
        }

        if ($variant->stock <= 0) {
            return back()->with('error', 'Product is out of stock.');
        }

        DB::transaction(function () use ($user, $variant, $request) {
            $cart = $user->cart()->firstOrCreate();
            $item = $cart->items()
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->lockForUpdate()
                ->first();
            $requestedQty = $request->integer('quantity');

            if ($item) {
                $newQty = min(
                    $item->quantity + $requestedQty,
                    $variant->stock
                );
                $item->update([
                    'quantity' => $newQty,
                ]);
                return;
            }

            $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => min(
                    $requestedQty,
                    $variant->stock
                ),
            ]);
        });

        return back()->with('success','Added to cart.');
    }

    public function update(CartItemUpdateRequest $request, CartItem $cartItem)
    {
        $cartItem->loadMissing('productVariant');

        $variant = $cartItem->productVariant;
        $quantity = $request->validated('quantity');

        if (! $variant) {
            return back()->with('error', 'Product variant no longer exists.');
        }

        if ($variant->stock <= 0) {
            return back()->with('error', 'Product is out of stock.');
        }

        if ($quantity > $variant->stock) {
            return back()->with('error', 'Only ' . $variant->stock . ' item(s) available.');
        }

        $cartItem->update([
            'quantity' => $quantity,
        ]);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
