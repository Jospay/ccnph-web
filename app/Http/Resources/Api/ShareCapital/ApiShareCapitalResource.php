<?php

namespace App\Http\Resources\Api\ShareCapital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiShareCapitalResource extends JsonApiResource
{
    public $attributes = [
        'amount',
        'term_months',
        'is_fully_paid',
        'total_paid',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'amount' => number_format($this->amount / 100, 2, '.', ''),
            'term_months' => $this->term_months,
            'is_fully_paid' => $this->isFullyPaid(),
            'total_paid' => number_format($this->total_paid / 100, 2, '.', ''),
            'status' => $this->status?->name ?? 'Unknown',
        ];
    }

    public $relationships = [
        'schedules' => ApiShareCapitalScheduleResource::class,
    ];
}
