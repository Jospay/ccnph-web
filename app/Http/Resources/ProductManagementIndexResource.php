<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductManagementIndexResource extends JsonResource
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
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'cooperative_scope' => $this->cooperative_scope?->value,
            'national_status' => $this->national_status?->value,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'sold_count' => $this->sold_count,
            'views' => $this->views,
            'thumbnail_url' => $this->thumbnail?->image
                ? Storage::url($this->thumbnail->image)
                : null,
            'price' => $this->defaultVariant?->price,
            'compare_price' => $this->defaultVariant?->compare_price,
            'total_stock' => (int) ($this->total_stock ?? 0),
            'shop_name' => $this->shop?->name,
            'cooperative_name' => $this->shop?->cooperative?->name,
        ];
    }
}
