<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Keep digits only (removes spaces, dashes and "+").
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => [
                'required',
                'string',
                'regex:/^(09|639)\d{9}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalized = str_starts_with($value, '63')
                        ? '0'.substr($value, 2)
                        : $value;

                    if (User::where('phone', $normalized)->exists()) {
                        $fail('This mobile number is already registered.');
                    }
                },
            ],
            'cooperative_id' => ['required', 'integer', 'exists:cooperatives,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'phone.required' => 'Mobile number is required.',
            'phone.regex' => 'Enter a valid mobile number (e.g. 09123456789).',
            'cooperative_id.required' => 'Please select a cooperative.',
            'cooperative_id.exists' => 'The selected cooperative does not exist.',
        ];
    }
}
