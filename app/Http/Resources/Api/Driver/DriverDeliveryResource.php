<?php

namespace App\Http\Resources\Api\Driver;

use App\Models\OrderDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderDelivery */
class DriverDeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $order = $this->order;
        $isCod = $order->isCod();

        $fullAddress = collect([
            $order->unit_bldg_house,
            $order->street,
            $order->barangay,
            $order->city,
            $order->province,
            $order->region,
            $order->postal_code,
        ])->filter()->implode(', ');

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'attempts' => $this->attempts,

            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'shop_name' => $order->store?->name,
                'total' => (float) $order->total,
                'note' => $order->notes,
                'items_count' => $order->items->sum('quantity'),
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                ])->values()->all(),
            ],

            // pickup = the shop, dropoff = the customer
            'recipient' => [
                'name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'address' => $fullAddress,
                'landmark' => $order->landmark,
                'region' => $order->region,
                'province' => $order->province,
                'city' => $order->city,
                'barangay' => $order->barangay,
            ],

            'payment' => [
                'is_cod' => $isCod,
                'collect_amount' => $isCod ? (float) $order->total : 0.0, // cash the driver must collect
            ],

            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'picked_up_at' => $this->picked_up_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'failure_reason' => $this->failure_reason,
            'proof_photo_url' => $this->proof_photo_url,
        ];
    }
}
