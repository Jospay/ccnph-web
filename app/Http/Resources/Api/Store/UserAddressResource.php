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
            'region_code' => $this->region_code,
            'province' => $this->province,
            'province_code' => $this->province_code,
            'city' => $this->city,
            'city_code' => $this->city_code,
            'barangay' => $this->barangay,
            'barangay_code' => $this->barangay_code,
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
