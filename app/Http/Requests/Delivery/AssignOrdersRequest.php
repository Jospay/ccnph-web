<?php

namespace App\Http\Requests\Delivery;

use App\Services\Delivery\DeliveryAssignmentService;
use Illuminate\Foundation\Http\FormRequest;

class AssignOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1', 'max:'.DeliveryAssignmentService::MAX_ORDERS_PER_ASSIGNMENT],
            'order_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
        ];
    }
}
