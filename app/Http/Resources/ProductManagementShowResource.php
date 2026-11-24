<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductManagementShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shop = $this->shop;
        $owner = $shop?->user;
        $cooperative = $shop?->cooperative;
        $ownerAddress = $owner
            ? collect([
                $owner->street,
                $owner->barangay,
                $owner->city,
                $owner->province,
                $owner->region,
                $owner->postal_code,
            ])->filter()->implode(', ')
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'cooperative_scope' => $this->cooperative_scope?->value,
            'national_status' => $this->national_status?->value,
            'national_reject_reason' => $this->national_reject_reason,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'sold_count' => $this->sold_count,
            'views' => $this->views,
            'created_at' => $this->created_at?->format('M d, Y h:i A'),

            'categories' => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->values(),

            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => Storage::url($image->image),
                'sort_order' => $image->sort_order,
            ])->values(),

            'variants' => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'is_default' => $variant->is_default,
                'price' => $variant->price,
                'compare_price' => $variant->compare_price,
                'stock' => $variant->stock,
                'weight' => $variant->weight,
                'image' => $variant->image ? Storage::url($variant->image) : null,
                'attributes' => $variant->attributeValues->map(fn ($value) => [
                    'id' => $value->id,
                    'value' => $value->value,
                ])->values(),
            ])->values(),

            'shop_name' => $shop?->name,
            'cooperative_name' => $cooperative?->name,
            'cooperative_logo_url' => $cooperative?->logo
                ? Storage::url($cooperative->logo)
                : null,

            'owner_name' => $owner?->name,
            'owner_email' => $owner?->email,
            'owner_address' => $ownerAddress ?: null,
        ];
    }
}
