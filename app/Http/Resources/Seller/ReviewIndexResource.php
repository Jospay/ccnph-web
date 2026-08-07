<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReviewIndexResource extends JsonResource
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
            'rating' => $this->rating,
            'comment' => $this->comment,
            'video' => $this->video ? Storage::url($this->video) : null,
            'reply' => $this->reply,
            'replied_at' => $this->replied_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => Storage::url($image->image),
            ]),
            'order_item' => [
                'product_name' => $this->orderItem->product_name,
                'product_image' => $this->orderItem->product_image
                    ? Storage::url($this->orderItem->product_image)
                    : null,
                'variant_name' => $this->orderItem->variant_name,
                'quantity' => $this->orderItem->quantity
            ],
            'user' => [
                'name' => $this->is_anonymous 
                    ? $this->maskName($this->user->name) 
                    : $this->user->name,
                'avatar' => $this->user->avatar
                    ? Storage::url($this->user->avatar)
                    : null
            ]
        ];
    }

    private function maskName(?string $name): string
    {
        if (!$name) {
            return '*****';
        }
        return mb_substr($name, 0, 1) . '***' . mb_substr($name, -1, 1);
    }
}