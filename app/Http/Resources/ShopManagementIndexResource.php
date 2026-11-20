<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopManagementIndexResource extends JsonResource
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
            'slug' => $this->slug,
            'name' => $this->name,
            'cooperative_name' => $this->cooperative->name ?? null,
            'user_name' => $this->user->name ?? null,
            'is_active' => $this->is_active,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'sold_count' => $this->sold_count,
        ];
    }
}
