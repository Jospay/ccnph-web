<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutSelectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['cart', 'direct'])],

            // Required if checking out from cart
            'cart_item_ids' => ['required_if:mode,cart', 'array'],
            'cart_item_ids.*' => ['integer', 'exists:cart_items,id'],

            // Required if using "Buy Now"
            'product_variant_id' => ['required_if:mode,direct', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required_if:mode,direct', 'integer', 'min:1'],
        ];
    }
}
