<?php

namespace App\Http\Resources\Api\Points;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiPointResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $isTampered = $this->isTampered();

        return [
            'id' => $this->id,
            'balance' => number_format($this->balance, 2, '.', ''),
            'show' => (bool) $this->show,
            'is_tampered' => $isTampered,
            'message' => $isTampered
                ? 'Your points balance integrity check failed. Please contact chat support for assistance.'
                : null,
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
