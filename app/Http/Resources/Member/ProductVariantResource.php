<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'stock' => $this->stock,
            'image' => $this->image,
            'is_default' => $this->is_default,
            'attributes' => $this->attributeValues
                ->map(fn ($value) => [
                    'attribute' => $value->attribute->name,
                    'value' => $value->value,
                ]),
        ];
    }
}
