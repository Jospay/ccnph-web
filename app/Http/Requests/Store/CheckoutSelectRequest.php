<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSelectRequest extends FormRequest
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
