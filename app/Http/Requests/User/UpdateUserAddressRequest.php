<?php

namespace App\Http\Requests\User;

use App\Enums\UserAddressLabel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', new Enum(UserAddressLabel::class)],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_number' => ['required', 'string', 'max:50'],
            'region' => ['required', 'string', 'max:255'],
            'region_code' => ['required', 'string', 'max:10'],
            'province' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'city_code' => ['required', 'string', 'max:10'],
            'barangay' => ['required', 'string', 'max:255'],
            'barangay_code' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'unit_bldg_house' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'landmark' => ['nullable', 'string'],
            'is_default' => ['required', 'boolean'],
        ];
    }
}
