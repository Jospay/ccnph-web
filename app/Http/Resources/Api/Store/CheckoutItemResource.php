<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CheckoutItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->productVariant;
        $product = $variant?->product;
        $store = $product?->store;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'product' => [
                'name' => $variant->product->name,
                'slug' => $variant->product->slug,
                'image' => $variant?->image
                    ? Storage::url($variant->image)
                    : (
                        $product?->images->first()?->image
                            ? Storage::url($product->images->first()->image)
                            : null
                    ),
                'store' => [
                    'name' => $store->name,
                    'slug' => $store->slug,
                ],
            ],

            'variant' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
            ],

            'attributes' => $variant
                ->attributeValues
                ->map(fn ($attributeValue) => [
                    'name' => $attributeValue->attribute->name,
                    'value' => $attributeValue->value,
                ]),

            'subtotal' => $this->quantity * $variant->price,
        ];
    }
}
