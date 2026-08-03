<?php

namespace App\Http\Requests\Seller\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user()->loadMissing(['shop']);
        return !$user->shop;
    }

    /**
     * Customize the forbidden response message.
     */
    public function messages(): array
    {
        return [
            'authorize' => 'You already have a shop.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:shops,name'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }
}
