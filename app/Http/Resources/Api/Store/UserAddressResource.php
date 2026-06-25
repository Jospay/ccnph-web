<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
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
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'recipient_number' => $this->recipient_number,
            'region' => $this->region,
            'province' => $this->province,
            'city' => $this->city,
            'barangay' => $this->barangay,
            'street' => $this->street,
            'postal_code' => $this->postal_code,
            'landmark' => $this->landmark,
            'unit_bldg_house' => $this->unit_bldg_house,
            'is_default' => $this->is_default,

            'full_address' => collect([
                $this->unit_bldg_house,
                $this->street,
                $this->barangay,
                $this->city,
                $this->province,
                $this->region,
                $this->postal_code,
            ])
                ->filter()
                ->implode(', '),
        ];
    }
}
