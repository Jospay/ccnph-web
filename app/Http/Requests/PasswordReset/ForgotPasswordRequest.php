<?php

namespace App\Http\Requests\PasswordReset;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

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

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'exists:users,phone'],
        ];
    }
}
