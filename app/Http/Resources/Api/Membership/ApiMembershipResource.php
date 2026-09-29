<?php

namespace App\Http\Resources\Api\Membership;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiMembershipResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'amount',
        'term_months',
        'activated_at',
        'expires_at',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'amount' => number_format($this->amount / 100, 2, '.', ''),
            'term_months' => $this->term_months,
            'activated_at' => $this->activated_at?->format('Y-m-d'),
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'status' => $this->status?->name ?? 'Unknown',
            'paid_schedules' => $this->whenLoaded(
                'schedules',
                fn () => $this->schedules->where('status_id', Status::PAID)->count()
            ),
            'unpaid_schedules' => $this->whenLoaded(
                'schedules',
                fn () => $this->schedules->whereIn('status_id', [
                    Status::UNPAID,
                    Status::OVERDUE,
                ])->count()
            ),
            'total_schedules' => $this->whenLoaded(
                'schedules',
                fn () => $this->schedules->count()
            ),
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'schedules' => ApiMembershipScheduleResource::class,
    ];
}
