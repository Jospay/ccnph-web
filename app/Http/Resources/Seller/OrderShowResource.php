<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Enums\OrderStatus;

class OrderShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isReturnGroup = in_array($this->status, [
            OrderStatus::RETURN_REQUESTED,
            OrderStatus::RETURN_APPROVED,
            OrderStatus::RETURNED,
        ], true);

        $allItems = $this->items;
        $displayItems = $isReturnGroup
            ? $allItems->filter(fn ($item) => $item->orderReturn !== null)->values()
            : $allItems;

        $subtotal = $isReturnGroup
            ? $displayItems->sum(fn ($item) => $item->price * $item->quantity)
            : (float) $this->subtotal;

        $itemsCount = $displayItems->sum('quantity');
        $shippingFee = $isReturnGroup ? 0.0 : (float) $this->shipping_fee;
        $total = $isReturnGroup ? round($subtotal, 2) : (float) $this->total;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'items_count'  => $itemsCount,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $this->discount,
            'total' => $total,
            'notes' => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'shipping_address' => [
                'recipient_name' => $this->recipient_name,
                'recipient_phone' => $this->recipient_phone,
                'region' => $this->region,
                'province' => $this->province,
                'city' => $this->city,
                'barangay' => $this->barangay,
                'street' => $this->street,
                'unit_bldg_house' => $this->unit_bldg_house,
                'postal_code' => $this->postal_code,
                'landmark' => $this->landmark,
            ],
            'items' => $displayItems->map(fn ($item) => [
                'product_sku' => $item->product_sku,
                'product_name' => $item->product_name,
                'product_image' => Storage::url($item->product_image),
                'variant_name' => $item->variant_name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => round($item->price * $item->quantity, 2),
                'return' => $item->orderReturn ? [
                    'reason' => $item->orderReturn->reason->value,
                    'reason_label' => $item->orderReturn->reason->label(),
                    'description' => $item->orderReturn->description,
                    'images' => $item->orderReturn->images
                        ->map(fn ($image) => Storage::url($image->image))
                        ->values(),
                    'video' => $item->orderReturn->video
                        ? Storage::url($item->orderReturn->video)
                        : null,
                    'rejection_reason' => $item->orderReturn->rejection_reason,
                    'created_at' => $item->orderReturn->created_at,
                ] : null,
            ]),
            // null ones dropped
            'timestamps' => array_filter([
                'confirmed_at' => $this->confirmed_at,
                'processing_at' => $this->processing_at,
                'packed_at' => $this->packed_at,
                'shipped_at' => $this->shipped_at,
                'delivered_at' => $this->delivered_at,
                'completed_at' => $this->completed_at,
                'cancelled_at' => $this->cancelled_at,
                'return_requested_at' => $this->return_requested_at,
                'return_approved_at' => $this->return_approved_at,
                'returned_at' => $this->returned_at,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}