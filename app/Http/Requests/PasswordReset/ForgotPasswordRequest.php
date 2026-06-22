<?php

namespace App\Http\Requests\PasswordReset;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Normalizes the incoming phone input to standard local format (09XXXXXXXXX)
     * to prevent validation failures when checking the users table.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $digits = preg_replace('/\D/', '', $this->phone);

            // +639XXXXXXXXX or 639XXXXXXXXX → 09XXXXXXXXX
            if (str_starts_with($digits, '63') && strlen($digits) === 12) {
                $digits = '0' . substr($digits, 2);
            }

            $this->merge([
                'phone' => $digits,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'exists:users,phone'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     * This replaces the default "The selected phone is invalid" message.
     */
    public function messages(): array
    {
        return [
            'phone.exists' => "We can't find a user with that phone number.",
        ];
    }
}
