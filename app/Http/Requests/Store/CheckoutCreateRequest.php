<?php

namespace App\Http\Requests\Store;

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
     */
    public function rules(): array
    {
        return [
            'mode' => [
                'nullable',
                'in:cart,direct',
            ],

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
                'max:500',
            ],

            'cart_item_ids' => [
                'required_if:mode,cart',
                'array',
            ],

            'cart_item_ids.*' => [
                'integer',
                'exists:cart_items,id',
            ],

            'product_variant_id' => [
                'required_if:mode,direct',
                'integer',
                'exists:product_variants,id',
            ],

            'quantity' => [
                'required_if:mode,direct',
                'integer',
                'min:1',
            ],
        ];
    }
}
