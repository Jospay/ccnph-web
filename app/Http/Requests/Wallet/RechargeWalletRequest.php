<?php

namespace App\Http\Requests\Wallet;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RechargeWalletRequest extends FormRequest
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
            'amount' => ['required', 'integer', 'min:10000'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'gateway_payment_method_id' => [
                'required_if:payment_method_id,' . PaymentMethod::CARD,
                'nullable',
                'string',
            ],
        ];
    }
}
