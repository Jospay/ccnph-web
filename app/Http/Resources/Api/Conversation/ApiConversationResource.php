<?php

namespace App\Http\Resources\Api\Conversation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiConversationResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'status',
        'created_at',
    ];

    /**
     * Transform the resource attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
