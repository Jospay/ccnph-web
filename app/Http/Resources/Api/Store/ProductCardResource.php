<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,

            'image' => $this->images->first()
                ? asset('storage/'.$this->images->first()->image)
                : null,

            'price' => $this->defaultVariant?->price,
            'compare_price' => $this->defaultVariant?->compare_price,
            'stock' => (int) ($this->total_stock ?? 0),
        ];
    }
}
