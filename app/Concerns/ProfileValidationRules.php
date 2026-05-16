<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'phone' => $this->phoneRules($userId),
            'gender' => $this->genderRules(),
            'birthdate' => $this->birthdateRules(),

            'region' => $this->regionRules(),
            'province' => $this->provinceRules(),
            'city' => $this->cityRules(),
            'barangay' => $this->barangayRules(),
            'street' => $this->streetRules(),
            'postal_code' => $this->postalCodeRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    protected function phoneRules(?int $userId = null): array
    {
        return [
            'nullable',
            'string',
            'max:20',

            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    protected function genderRules(): array
    {
        return [
            'nullable',
            Rule::in([
                'Male',
                'Female',
                'Other',
                'Prefer not to say',
            ]),
        ];
    }

    protected function birthdateRules(): array
    {
        return [
            'nullable',
            'date',
            'before:today',
        ];
    }

    protected function regionRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    protected function provinceRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    protected function cityRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    protected function barangayRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    protected function streetRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    protected function postalCodeRules(): array
    {
        return [
            'nullable',
            'string',
            'max:20',
        ];
    }
}
