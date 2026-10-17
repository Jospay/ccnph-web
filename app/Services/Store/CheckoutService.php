<?php

namespace App\Services\Store;

use App\Enums\CheckoutStatus;
use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Status;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public const PAYMENT_STATUS_PENDING = Status::PENDING;

    // same "paid" code the app/webhook already use (points load polls for 11)
    public const PAYMENT_STATUS_PAID = 11;

    public const PAYMENT_STATUS_FAILED = Status::FAILED;

    /**
     * @param  bool  $decrementStock  Kept for compatibility. Stock is decremented by the caller.
     * @param  array  $meta  Stored on the payment (e.g. cart_item_ids to clear once paid)
     */
    public function createCheckout(
        User $user,
        UserAddress $address,
        PaymentMethod $paymentMethod,
        Collection $cartItems,
        ?string $note = null,
        bool $decrementStock = false,
        array $meta = [],
    ): Checkout {
        return DB::transaction(function () use (
            $user, $address, $paymentMethod, $cartItems, $note, $meta
        ) {
            $pricing = app(CheckoutPricingService::class);

            $usingPoints = $paymentMethod->id === PaymentMethod::POINT;

            $variants = ProductVariant::query()
                ->lockForUpdate()
                ->with(['product.store', 'attributeValues'])
                ->whereIn('id', $cartItems->pluck('product_variant_id'))
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
                fn ($item) => $variants[$item->product_variant_id]->product->store->id
            );

            $grandTotal = 0;

            $checkout = Checkout::create([
                'user_id' => $user->id,
                'checkout_number' => 'CHK-'.Str::upper(Str::random(12)),
                'grand_total' => 0,
                'status' => CheckoutStatus::PENDING_PAYMENT,
            ]);

            foreach ($itemsByStore as $shopId => $storeItems) {
                $subtotal = (float) $storeItems->sum(
                    fn ($item) => $item->quantity * $variants[$item->product_variant_id]->price
                );

                $shippingFee = CheckoutPricingService::SHIPPING_FEE; // temporary shipping logic

                // membership / shared capital (always) + point (only when paying with points)
                $discounts = $pricing->forStore($user, $subtotal, $usingPoints);

                $total = round($subtotal + $shippingFee - $discounts['total'], 2);
                $grandTotal += $total;

                $order = $checkout->orders()->create([
                    'user_id' => $user->id,
                    'shop_id' => $shopId,
                    'order_number' => 'ORD-'.Str::upper(Str::random(12)),
                    'status' => OrderStatus::PENDING,
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'membership_discount' => $discounts['membership'],
                    'shared_capital_discount' => $discounts['shared_capital'],
                    'point_discount' => $discounts['point'],
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
                    $variant = $variants[$cartItem->product_variant_id];

                    $order->items()->create([
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'product_sku' => $variant->sku,
                        'product_name' => $variant->product->name,
                        'product_image' => $variant->image,
                        'variant_name' => $variant->attributeValues->pluck('value')->implode(' / '),
                        'price' => $variant->price,
                        'quantity' => $cartItem->quantity,
                    ]);
                }
            }

            $grandTotal = round($grandTotal, 2);

            $checkout->update(['grand_total' => $grandTotal]);

            // COD keeps its old pesos amount; points / gateway payments are stored in cents
            $isCod = $paymentMethod->id === PaymentMethod::CASH_ON_DELIVERY;

            // Online processing fee (TransactionFee "Store"), charged on top of the order
            // total through the gateway. payments.amount stays the order total.
            $feeCents = $paymentMethod->isOnline()
                ? (int) round($pricing->onlineFee($grandTotal) * 100)
                : 0;

            $payment = Payment::create([
                'payable_type' => Checkout::class,
                'payable_id' => $checkout->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $isCod ? $grandTotal : (int) round($grandTotal * 100),
                'fee' => $feeCents,
                'status_id' => self::PAYMENT_STATUS_PENDING,
                'payment_date' => now(),
                'meta' => $meta ?: null,
            ]);

            $checkout->setRelation('payment', $payment);

            return $checkout;
        });
    }

    public function paymentFor(Checkout $checkout): ?Payment
    {
        return Payment::where('payable_type', Checkout::class)
            ->where('payable_id', $checkout->id)
            ->latest('id')
            ->first();
    }

    /**
     * Idempotent: safe to call from the webhook, the status poll and the points flow.
     */
    public function markAsPaid(Checkout $checkout): void
    {
        DB::transaction(function () use ($checkout) {
            $locked = Checkout::whereKey($checkout->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === CheckoutStatus::PAID) {
                return;
            }

            $payment = $this->paymentFor($locked);

            if ($payment) {
                $payment->update([
                    'status_id' => self::PAYMENT_STATUS_PAID,
                    'payment_date' => now(),
                ]);

                // online checkouts keep the cart until paid
                if ($ids = data_get($payment->meta, 'cart_item_ids')) {
                    CartItem::whereIn('id', $ids)->delete();
                }
            }

            $locked->update(['status' => CheckoutStatus::PAID]);
        });
    }

    /**
     * Idempotent: cancel an unpaid checkout and put the reserved stock back.
     */
    public function markAsFailed(Checkout $checkout): void
    {
        DB::transaction(function () use ($checkout) {
            $locked = Checkout::whereKey($checkout->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === CheckoutStatus::PAID || $locked->status === CheckoutStatus::CANCELLED) {
                return;
            }

            $payment = $this->paymentFor($locked);

            if ($payment && (int) $payment->status_id === self::PAYMENT_STATUS_PAID) {
                return;
            }

            $locked->load('orders.items');

            foreach ($locked->orders as $order) {
                foreach ($order->items as $item) {
                    ProductVariant::whereKey($item->product_variant_id)
                        ->increment('stock', $item->quantity);
                }

                $order->update(['status' => OrderStatus::CANCELLED]);
            }

            $payment?->update(['status_id' => self::PAYMENT_STATUS_FAILED]);

            $locked->update(['status' => CheckoutStatus::CANCELLED]);
        });
    }

    /**
     * Release stock of online checkouts nobody paid for.
     */
    public function expireStale(int $minutes = 30): int
    {
        $count = 0;

        Checkout::where('status', CheckoutStatus::PENDING_PAYMENT)
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->whereHas('payment', fn ($q) => $q->whereIn('payment_method_id', PaymentMethod::ONLINE_METHODS))
            ->each(function (Checkout $checkout) use (&$count) {
                $this->markAsFailed($checkout);
                $count++;
            });

        return $count;
    }
}
