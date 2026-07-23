<?php

namespace App\Http\Resources\Api\Store;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'total' => (float) $this->total,
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_image' => $item->product_image ? asset('storage'.$item->product_image) : null,
                'variant_name' => $item->variant_name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
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
            'is_rated' => (bool) $this->is_rated, // <-- ADD THIS
        ];
    }
}
