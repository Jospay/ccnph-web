<?php

namespace App\Http\Resources\Seller;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isReturnOrder = in_array(
            $this->status,
            [
                OrderStatus::RETURN_REQUESTED,
                OrderStatus::RETURN_APPROVED,
                OrderStatus::RETURNED,
            ],
            true
        );

        $items = $isReturnOrder
            ? $this->items
                ->filter(fn ($item) => $item->orderReturn)
                ->values()
            : $this->items;

        $subtotal = $isReturnOrder
            ? $items->sum(fn ($item) => $item->price * $item->quantity)
            : (float) $this->subtotal;

        $itemsCount = $items->sum('quantity');

        $mappedItems = $items->map(function ($item) {
            return [
                'product_name'  => $item->product_name,
                'product_image' => $item->product_image
                    ? Storage::url($item->product_image)
                    : null,
                'product_sku'   => $item->product_sku,
                'variant_name'  => $item->variant_name,
                'price'         => (float) $item->price,
                'quantity'      => $item->quantity,
                'total'         => (float) ($item->price * $item->quantity),
            ];
        })->values();

        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'status_label' => $this->status->label(),
            'items_count'  => $itemsCount,
            'subtotal'     => $subtotal,
            'shipping_fee' => $isReturnOrder ? 0.0 : (float) $this->shipping_fee,
            'discount'     => (float) $this->discount,
            'total'        => $isReturnOrder ? round($subtotal, 2) : (float) $this->total,
            'items'        => $mappedItems,
        ];
    }
}