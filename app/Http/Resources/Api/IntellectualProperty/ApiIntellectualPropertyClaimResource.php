<?php

namespace App\Http\Resources\Api\IntellectualProperty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiIntellectualPropertyClaimResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'description',
        'created_at',
        'updated_at',
    ];

    /**
     * Transform the resource attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'description' => $this->description,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'intellectual_property' => ApiIntellectualPropertyResource::class,
    ];
}
