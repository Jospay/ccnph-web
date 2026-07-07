<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IntellectualPropertyResource extends JsonResource
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
            'status_name' => $this->status->name,
            'user_name' => $this->user->name ?? 'N/A',
            'creation_type' => $this->creation_type,
            'form_type' => $this->form_type,
            'title' => $this->title,
            'conversation' => $this->whenLoaded('conversation', fn() => new ConversationResource($this->conversation)),
        ];
    }
}
