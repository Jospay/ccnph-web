<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartResource extends JsonResource
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
            'items' => $this->items->map(function ($item) {
                // Accessing the relation loaded via controller eager loading
                $variant = $item->productVariant;
                $product = $variant?->product;
                $store = $product?->store;

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'product_variant_id' => $item->product_variant_id,

                    // Maps exactly to TypeScript interface "CartVariant"
                    'variant' => $variant ? [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => (float) $variant->price, // Cast to float for JS arithmetic
                        'compare_price' => $variant->compare_price ? (float) $variant->compare_price : null,
                        'stock' => $variant->stock,
                        'image' => $variant->image ? Storage::url($variant->image) : null,
                        'attributes' => $variant->attributeValues->map(fn ($attrValue) => [
                            'name' => $attrValue->attribute->name,
                            'value' => $attrValue->value,
                        ])->values()->toArray(),
                    ] : null,

                    // Maps exactly to TypeScript interface "CartProduct"
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        // Adding seller dynamically from your store relation
                        'seller' => $store?->name ?? 'FISMPC Store',
                    ] : null,
                ];
            })->values()->toArray(),
        ];
    }
}
