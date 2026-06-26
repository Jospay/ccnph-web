<?php

namespace App\Http\Resources\Member;

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
        $itemsCount = 0;
        
        $mappedItems = $this->items->map(function ($item) use (&$itemsCount) {
            $itemsCount += $item->quantity;
            $itemTotal = $item->price * $item->quantity;

            return [
                'product_name'  => $item->product_name,
                'product_image' => $item->product_image ? Storage::url($item->product_image) : null,
                'product_sku'   => $item->product_sku,
                'variant_name'  => $item->variant_name,
                'price'         => (float) $item->price,
                'quantity'      => $item->quantity,
                'total'         => (float) $itemTotal,
            ];
        })->values();

        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'status_label' => $this->status->label(),
            'items_count'  => $itemsCount,
            'subtotal'     => (float) $this->subtotal,
            'shipping_fee' => (float) $this->shipping_fee,
            'discount'     => (float) $this->discount,
            'total'        => (float) $this->total,
            'items'        => $mappedItems,
        ];
    }
}
