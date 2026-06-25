<?php

namespace App\Services\Store;

use App\Enums\CheckoutStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Checkout;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function createCheckout(
        User $user,
        UserAddress $address,
        PaymentMethod $paymentMethod,
        Collection $cartItems,
        ?string $note = null,
        bool $decrementStock = false,
    ): Checkout {
        return DB::transaction(function () use (
            $user,
            $address,
            $paymentMethod,
            $cartItems,
            $note,
            $decrementStock,
        ) {

            $variants = ProductVariant::query()
                ->lockForUpdate()
                ->with([
                    'product.store',
                    'attributeValues',
                ])
                ->whereIn(
                    'id',
                    $cartItems->pluck('product_variant_id')
                )
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $cartItem) {
                $variant = $variants[$cartItem->product_variant_id];

                if ($variant->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'checkout' => "{$variant->product->name} has insufficient stock.",
                    ]);
                }
            }

            $itemsByStore = $cartItems->groupBy(
                fn ($item) => $variants[$item->product_variant_id]
                    ->product
                    ->store_id
            );

            $grandTotal = 0;

            $checkout = Checkout::create([
                'user_id' => $user->id,
                'checkout_number' => 'CHK-'.Str::upper(Str::random(12)),
                'grand_total' => 0,
                'status' => CheckoutStatus::PENDING_PAYMENT,
            ]);

            foreach ($itemsByStore as $storeId => $storeItems) {

                $subtotal = $storeItems->sum(
                    fn ($item) => $item->quantity *
                        $variants[$item->product_variant_id]->price
                );

                // temporary shipping logic
                $shippingFee = 60;

                $discount = 0;

                $total = $subtotal
                    + $shippingFee
                    - $discount;

                $grandTotal += $total;

                $order = $checkout->orders()->create([
                    'user_id' => $user->id,
                    'store_id' => $storeId,

                    'order_number' => 'ORD-'.Str::upper(Str::random(12)),

                    'status' => OrderStatus::PENDING,

                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount' => $discount,
                    'total' => $total,

                    'notes' => $note,

                    'recipient_name' => $address->recipient_name,
                    'recipient_phone' => $address->recipient_number,

                    'region' => $address->region,
                    'province' => $address->province,
                    'city' => $address->city,
                    'barangay' => $address->barangay,
                    'street' => $address->street,
                    'unit_bldg_house' => $address->unit_bldg_house,
                    'postal_code' => $address->postal_code,
                    'landmark' => $address->landmark,
                ]);

                foreach ($storeItems as $cartItem) {

                    $variant = $variants[
                        $cartItem->product_variant_id
                    ];

                    $order->items()->create([
                        'product_id' => $variant->product_id,

                        'product_variant_id' => $variant->id,

                        'product_sku' => $variant->sku,

                        'product_name' => $variant->product->name,

                        'product_image' => $variant->image,

                        'variant_name' => $variant
                            ->attributeValues
                            ->pluck('value')
                            ->implode(' / '),

                        'price' => $variant->price,

                        'quantity' => $cartItem->quantity,
                    ]);

                    if ($decrementStock) {
                        $variant->decrement(
                            'stock',
                            $cartItem->quantity
                        );
                    }
                }
            }

            $checkout->update([
                'grand_total' => $grandTotal,
            ]);

            $checkout->payment()->create([
                'payment_method_id' => $paymentMethod->id,
                'amount' => $grandTotal,
                'status' => PaymentStatus::PENDING,
            ]);

            return $checkout->fresh([
                'orders.items',
                'payment',
            ]);
        });
    }
}
