<?php

namespace App\Http\Requests\Delivery;

use App\Models\Driver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DriverStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protected by the role middleware on the route group
    }

    public function rules(): array
    {
        /** @var Driver|null $driver (set on update routes) */
        $driver = $this->route('driver');

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'contact_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('drivers', 'contact_number')->ignore($driver?->id)->withoutTrashed(),
            ],
            // required when creating, optional when editing
            'password' => [
                $driver ? 'nullable' : 'required',
                'string',
                Password::min(8),
            ],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],

            'areas' => ['required', 'array', 'min:1'],
            'areas.*.region' => ['required', 'string', 'max:150'],
            'areas.*.region_code' => ['nullable', 'string', 'max:20'],
            'areas.*.province' => ['required', 'string', 'max:150'],
            'areas.*.province_code' => ['nullable', 'string', 'max:20'],
            'areas.*.city' => ['required', 'string', 'max:150'],
            'areas.*.city_code' => ['nullable', 'string', 'max:20'],
            'areas.*.barangay' => ['nullable', 'string', 'max:150'],
            'areas.*.barangay_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
