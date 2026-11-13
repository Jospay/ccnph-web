<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug ?? $this->gateway_type,
            'name' => $this->name,
            'gateway_type' => $this->gateway_type,
        ];
    }
}
