<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CartItemCreateRequest;
use App\Http\Requests\Store\CartItemUpdateRequest;
use App\Http\Resources\Api\Store\CartResource;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCartController extends Controller
{
    /**
     * GET
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cart = $user->cart()->firstOrCreate();

        $cart->load([
            'items.productVariant.product.store',
            'items.productVariant.product.images',
            'items.productVariant.attributeValues.attribute',
        ]);

        return response()->json([
            'success' => true,
            'cart' => new CartResource($cart),
        ]);
    }

    /**
     * POST
     */
    public function store(CartItemCreateRequest $request)
    {
        $user = $request->user();

        $variant = ProductVariant::with('product')
            ->findOrFail($request->integer('product_variant_id'));

        if (! $variant->product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product is unavailable.',
            ], 422);
        }

        if ($variant->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock.',
            ], 422);
        }

        DB::transaction(function () use (
            $user,
            $variant,
            $request
        ) {
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

        $cart = $user->cart()->first();

        $cart->load([
            'items.productVariant.product.store',
            'items.productVariant.product.images',
            'items.productVariant.attributeValues.attribute',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to cart.',
            'cart' => new CartResource($cart),
        ]);
    }

    /**
     * PATCH
     */
    public function update(
        CartItemUpdateRequest $request,
        CartItem $cartItem
    ) {
        if (
            $cartItem->cart->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $cartItem->load('productVariant');

        $variant = $cartItem->productVariant;

        $quantity = $request->validated('quantity');

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant no longer exists.',
            ], 404);
        }

        if ($variant->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock.',
            ], 422);
        }

        if ($quantity > $variant->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Only '.$variant->stock.' item(s) available.',
            ], 422);
        }

        $cartItem->update([
            'quantity' => $quantity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
        ]);
    }

    /**
     * DELETE
     */
    public function destroy(
        Request $request,
        CartItem $cartItem
    ) {
        if (
            $cartItem->cart->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }
}
