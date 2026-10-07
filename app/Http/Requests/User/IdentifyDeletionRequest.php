<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class IdentifyDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
