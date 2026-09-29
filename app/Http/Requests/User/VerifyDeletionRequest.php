<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp_code' => ['required', 'string'],
        ];
    }
}
