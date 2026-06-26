<?php

namespace App\Http\Resources\Api\Store;

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
        return [
            'id' => $this->id,
            'store_name' => $this->store?->name,
            'status' => match ($this->status) {
                OrderStatus::PENDING => 'to-pay',
                OrderStatus::CONFIRMED => 'to-ship',
                OrderStatus::PROCESSING => 'to-ship',
                OrderStatus::PACKED => 'to-ship',
                OrderStatus::SHIPPED => 'to-receive',
                OrderStatus::DELIVERED => 'completed',
                OrderStatus::CANCELLED => 'cancelled',
                OrderStatus::RETURNED => 'returned',
                default => 'all'
            },
            'status_label' => method_exists($this->status, 'label') ? $this->status->label() : str_replace('_', ' ', $this->status->value ?? $this->status),
            'shipping_fee' => (float) $this->shipping_fee,
            'total' => (float) $this->total,
            'items' => $this->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'product_image' => $item->product_image ? Storage::url($item->product_image) : null,
                'variant_name' => $item->variant_name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
            ])->values()->all(),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
