<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShopManagementShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cooperative = $this->cooperative;
        $owner = $this->user;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_official' => $this->is_official,
            'created_at' => $this->created_at?->format('M d, Y h:i A'),

            'cooperative_name' => $cooperative?->name,
            'cooperative_logo_url' => $cooperative?->logo
                ? Storage::url($cooperative->logo)
                : null,

            'owner_name' => $owner?->name,
            'owner_email' => $owner?->email,
            'owner_phone' => $owner?->phone !== null ? (string) $owner->phone : null,
        ];
    }
}
