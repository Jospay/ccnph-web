<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->emptyToNull($this->phone),
            'gender' => $this->emptyToNull($this->gender),
            'birthdate' => $this->emptyToNull($this->birthdate),

            'region' => $this->emptyToNull($this->region),
            'province' => $this->emptyToNull($this->province),
            'city' => $this->emptyToNull($this->city),
            'barangay' => $this->emptyToNull($this->barangay),
            'street' => $this->emptyToNull($this->street),
            'postal_code' => $this->emptyToNull($this->postal_code),
        ]);
    }

    /**
     * Convert empty string to null.
     */
    protected function emptyToNull(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()->id);
    }
}
