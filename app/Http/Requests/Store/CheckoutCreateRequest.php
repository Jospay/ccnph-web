<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                'exists:user_addresses,id',
            ],
            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];

    }
}
