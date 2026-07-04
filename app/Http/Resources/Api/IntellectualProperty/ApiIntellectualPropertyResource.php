<?php

namespace App\Http\Resources\Api\IntellectualProperty;

use App\Http\Resources\Api\Conversation\ApiConversationResource;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiIntellectualPropertyResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'creation_type',
        'form_type',
        'title',
        'description',
        'applicability',
        'amount',
        'term_months',
        'activated_at',
        'expires_at',
        'status',
    ];

    /**
     * The resource's attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'creation_type' => $this->creation_type,
            'form_type' => $this->form_type,
            'title' => $this->title,
            'description' => $this->description,
            'applicability' => $this->applicability,
            'amount' => number_format($this->amount / 100, 2, '.', ''),
            'term_months' => $this->term_months,
            'activated_at' => $this->activated_at?->format('Y-m-d'),
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'status' => $this->status?->name ?? 'Unknown',
            'paid_schedules' => $this->whenLoaded(
                'schedules',
                fn() => $this->schedules->where('status_id', Status::PAID)->count()
            ),
            'unpaid_schedules' => $this->whenLoaded(
                'schedules',
                fn() => $this->schedules->whereIn('status_id', [
                    Status::UNPAID,
                    Status::OVERDUE,
                ])->count()
            ),
            'total_schedules' => $this->whenLoaded(
                'schedules',
                fn() => $this->schedules->count()
            ),
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'schedules' => ApiIntellectualPropertyScheduleResource::class,
        'claims' => ApiIntellectualPropertyClaimResource::class,
        'documents' => ApiIntellectualPropertyDocumentResource::class,
        'setting' => ApiIntellectualPropertySettingResource::class,
        'payments' => ApiIntellectualPropertyPaymentResource::class,
        'conversation' => ApiConversationResource::class,
    ];
}
