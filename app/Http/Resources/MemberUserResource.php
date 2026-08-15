<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberUserResource extends JsonResource
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
            'email' => $this->email ?? '-',
            'phone' => $this->phone ?? '-',
            'address' => implode(', ', $addressParts) ?: '-',
            'status_name' => $this->status->name ?? '-',
            'user_type_name' => $this->userType->name ?? '-',
        ];
    }
}
