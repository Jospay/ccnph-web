<?php

namespace App\Http\Requests\PasswordReset;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');
        if ($phone) {
            $digits = preg_replace('/\D/', '', $phone);
            if (str_starts_with($digits, '63') && strlen($digits) === 12) {
                $digits = '0' . substr($digits, 2);
            }
            $this->merge(['phone' => $digits]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'exists:password_reset_requests,phone'],
            'otp_code' => ['required', 'string'],
        ];
    }
}
