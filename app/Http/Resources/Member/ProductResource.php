<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\ProductVariantResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $stocks = $this->variants->sum('stock');
        $prices = $this->variants->pluck('price');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'thumbnail' => $this->images->first()?->image,
            'categories' => $this->categories->pluck('name'),
            'variant_count' => $this->variants->count(),
            'total_stock' => $stocks,
            'min_price' => $prices->min(),
            'max_price' => $prices->max(),
            'views' => $this->views,

            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),
        ];
    }
}
