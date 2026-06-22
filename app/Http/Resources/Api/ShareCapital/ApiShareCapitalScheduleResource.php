<?php

namespace App\Http\Resources\Api\ShareCapital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApiShareCapitalScheduleResource extends JsonApiResource
{
    public $attributes = [
        'installment_no',
        'amount',
        'due_date',
        'status',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'installment_no' => $this->installment_no,
            'amount' => number_format($this->amount / 100, 2, '.', ''),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status?->name ?? 'Unknown',
        ];
    }

    public $relationships = [
        'payments' => ApiShareCapitalPaymentResource::class,
    ];
}
