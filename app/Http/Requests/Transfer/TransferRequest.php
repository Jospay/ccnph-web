<?php

namespace App\Http\Requests\Transfer;

use App\Services\Transfer\TransferService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $min = app(TransferService::class)->getMinTransfer();

        return [
            'channel_id' => [
                'required',
                Rule::exists('transaction_channels', 'code')->where('is_active', true),
            ],
            'amount' => ['required', 'numeric', 'min:'.$min],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'destination_bic' => ['nullable', 'string', 'max:20'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'verification_method' => ['required', Rule::in(['password', 'biometric'])],
            'password' => ['required_if:verification_method,password', 'nullable', 'string'],
            'device_id' => ['required_if:verification_method,biometric', 'nullable', 'string'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'password.required_if' => 'Password is required for password verification.',
            'device_id.required_if' => 'Device ID is required for biometric verification.',
        ];
    }
}
