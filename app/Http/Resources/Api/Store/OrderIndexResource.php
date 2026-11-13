<?php

namespace App\Http\Resources\Api\Store;

use App\Enums\OrderStatus;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderIndexResource extends JsonResource
{
    /**
     * Total Payment of this order = order total + its share of the online
     * processing fee (same number the track order screen shows).
     *
     * payments.fee is stored in cents and belongs to the whole checkout, so when a
     * checkout has several shop orders the fee is split in proportion to each
     * order's total. COD / Points have no fee.
     */
    private function onlineFeeShare(): float
    {
        $checkout = $this->checkout;
        $payment = $checkout?->payment;

        if (! $payment) {
            return 0.0;
        }

        if (! in_array((int) $payment->payment_method_id, PaymentMethod::ONLINE_METHODS, true)) {
            return 0.0;
        }

        $checkoutFee = round(((int) ($payment->fee ?? 0)) / 100, 2);
        $checkoutTotal = (float) ($checkout->grand_total ?? 0);

        if ($checkoutTotal <= 0) {
            return $checkoutFee;
        }

        return round($checkoutFee * ((float) $this->total / $checkoutTotal), 2);
    }

    public function toArray(Request $request): array
    {
        $fee = $this->onlineFeeShare();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'store_name' => $this->store?->name,
            'status' => match ($this->status) {
                OrderStatus::PENDING => 'to-pay',
                OrderStatus::CONFIRMED,
                OrderStatus::PROCESSING,
                OrderStatus::PACKED => 'to-ship',
                OrderStatus::SHIPPED => 'shipped',
                OrderStatus::DELIVERED => 'delivered',
                OrderStatus::COMPLETED => 'completed',
                OrderStatus::CANCELLED => 'cancelled',
                OrderStatus::RETURN_REQUESTED => 'return_requested',
                OrderStatus::RETURN_APPROVED => 'return_approved',
                OrderStatus::RETURNED => 'returned',
                default => 'all'
            },
            'raw_status' => $this->status->value ?? $this->status,
            'status_label' => method_exists($this->status, 'label')
                ? $this->status->label()
                : str_replace('_', ' ', $this->status->value ?? $this->status),
            'shipping_fee' => (float) $this->shipping_fee,
            // order total after discounts, without the online fee
            'total' => (float) $this->total,
            // online processing fee share of this order (0 for COD / Points)
            'online_fee' => $fee,
            // Total Payment = total + online fee (what the customer actually pays)
            'amount' => round((float) $this->total + $fee, 2),
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_image' => $item->product_image ? asset('storage/'.$item->product_image) : null,
                'variant_name' => $item->variant_name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
                'cooperative_id' => $item->product?->cooperative_id,
                'cooperative_scope' => $item->product?->cooperative_scope?->value,
            ])->values()->all(),
            'tracking' => [
                'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
                'confirmed_at' => $this->confirmed_at ? $this->confirmed_at->toIso8601String() : null,
                'processing_at' => $this->processing_at ? $this->processing_at->toIso8601String() : null,
                'packed_at' => $this->packed_at ? $this->packed_at->toIso8601String() : null,
                'shipped_at' => $this->shipped_at ? $this->shipped_at->toIso8601String() : null,
                'delivered_at' => $this->delivered_at ? $this->delivered_at->toIso8601String() : null,
                'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->toIso8601String() : null,
                'returned_at' => $this->returned_at ? $this->returned_at->toIso8601String() : null,
            ],
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,

            // Evaluates dynamically if dynamically set attribute isn't found
            'is_rated' => $this->is_rated ?? (
                ($this->status === OrderStatus::COMPLETED || $this->status === OrderStatus::COMPLETED->value)
                && $this->items->isNotEmpty()
                && $this->items->every(fn ($item) => $item->relationLoaded('review') && $item->review !== null)
            ),
        ];
    }
}
