<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; 

class ProductEditResource extends JsonResource
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
            'description' => $this->description,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'category_ids' => $this->categories
                ->pluck('id')
                ->values(),
            'images' => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url'        => Storage::url($img->image),
                'sort_order' => $img->sort_order,
            ]),
            'video' => $this->video
                ? asset('storage/' . $this->video)
                : null,
            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'compare_price' => $variant->compare_price,
                    'stock' => $variant->stock,
                    'weight' => $variant->weight,
                    'is_default' => $variant->is_default,
                    'image_url' => $variant->image
                        ? Storage::url($variant->image)
                        : null,
                    'attributes' => $variant
                        ->attributeValues
                        ->map(fn ($value) => [
                            'attribute_id' => $value->attribute_id,
                            'value_id' => $value->id,
                            'value' => $value->value,
                            'is_new' => false,
                        ])
                        ->values(),
                ];
            })->values(),
        ];
    }
}
