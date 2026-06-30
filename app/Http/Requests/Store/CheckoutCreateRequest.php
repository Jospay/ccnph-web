<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:user_addresses,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'note' => ['nullable', 'string', 'max:1000'],

            'mode' => ['required', Rule::in(['cart', 'direct'])],

            // Cart conditional fields
            'cart_item_ids' => ['required_if:mode,cart', 'array'],
            'cart_item_ids.*' => ['integer', 'exists:cart_items,id'],

            // Direct conditional fields
            'product_variant_id' => ['required_if:mode,direct', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required_if:mode,direct', 'integer', 'min:1'],
        ];
    }
}
