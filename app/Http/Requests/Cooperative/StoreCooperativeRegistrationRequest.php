<?php

namespace App\Http\Requests\Cooperative;

use App\Models\CooperativeRegister;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreCooperativeRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phone = ['required', 'string', 'regex:/^(09|\+?639)\d{9}$/'];

        return [
            // Cooperative
            'cooperative_name' => ['required', 'string', 'max:255'],
            'acronym' => ['nullable', 'string', 'max:50'],
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],

            // Unverified (pending_verification) rows don't block a CDA number;
            // they are abandoned drafts and get cleaned up once someone verifies.
            'cda_registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cooperative_registers', 'cda_registration_number')
                    ->where(fn ($query) => $query->where('status', '!=', CooperativeRegister::STATUS_PENDING_VERIFICATION)),
            ],
            'cooperative_type' => ['required', 'string', 'in:multi_purpose,credit,consumers,producers,service,transport,housing,other'],
            'cda_certificate' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
            'coop_email' => ['required', 'email', 'max:255'],
            'coop_contact' => $phone,

            // Address (all required; province is skipped for regions without provinces, e.g. NCR)
            'region' => ['required', 'string', 'max:100'],
            'province' => [
                Rule::requiredIf(fn () => ! $this->regionHasNoProvinces()),
                'nullable',
                'string',
                'max:100',
            ],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'street_address' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:10'],

            // Leader / web admin
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'in:chairperson,general_manager,board_secretary,admin_officer'],
            // Users only exist after OTP verification, so a pending registration
            // never blocks its own email / phone here.
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact' => array_merge($phone, ['unique:users,phone']),
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'coop_contact.regex' => 'Enter a valid PH mobile number (e.g. 09171234567).',
            'contact.regex' => 'Enter a valid PH mobile number (e.g. 09171234567).',
            'contact.unique' => 'This mobile number is already registered.',
            'email.unique' => 'This email is already registered.',
            'cda_registration_number.unique' => 'This CDA registration number is already registered.',
            'region.required' => 'Please select a region.',
            'province.required' => 'Please select a province.',
            'city.required' => 'Please select a city / municipality.',
            'barangay.required' => 'Please select a barangay.',
            'street_address.required' => 'Please enter your street address / building.',
            'zip_code.required' => 'Please enter your zip code.',
        ];
    }

    private function regionHasNoProvinces(): bool
    {
        $region = Str::lower((string) $this->input('region'));

        return str_contains($region, 'national capital') || str_contains($region, 'ncr');
    }
}
