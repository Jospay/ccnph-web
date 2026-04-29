<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MemberUserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $addressParts = array_filter([
            $this->street,
            $this->barangay,
            $this->city,
            $this->province,
            $this->region,
            $this->postal_code,
        ]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : Storage::url('avatars/default.png'),
            'email' => $this->email,
            'phone' => $this->phone ?? '-',
            'gender' => $this->gender ?? '-',
            'address' => implode(', ', $addressParts) ?: '-',
            'status_name' => $this->status->name ?? '-',
            'user_type_name' => $this->userType->name ?? '-',
            'valid_id_type' => $this->valid_id_type ?? '-',
            'valid_id_number' => $this->valid_id_number ?? '-',
            'front_id_url' => $this->front_valid_id_picture ? Storage::url($this->front_valid_id_picture) : null,
            'back_id_url' => $this->back_valid_id_picture ? Storage::url($this->back_valid_id_picture) : null,
            'created_at' => $this->created_at?->format('M d, Y'),
        ];
    }
}
