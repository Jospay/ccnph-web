<?php

namespace App\Http\Requests\Points;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class LoadPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'gateway_payment_method_id' => [
                'required_if:payment_method_id,'.PaymentMethod::CARD,
                'nullable',
                'string',
            ],
        ];
    }
}
