<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // 'name' => 'required|string|max:255',
            // 'phone' => "required|string|max:20|unique:users,phone,{$userId}",

            'gender' => [
                'required',
                'in:Male,Female,Other,Prefer not to say',
            ],

            'birthdate' => [
                'required',
                'date',
                'before:today',
            ],

            'email' => [
                'required',
                'email',
                "unique:users,email,{$userId}",
            ],

            'region' => [
                'required',
                'string',
            ],

            'province' => [
                'nullable',
                'string',
            ],

            'city' => [
                'required',
                'string',
            ],

            'barangay' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | VALID ID TYPE
            |--------------------------------------------------------------------------
            */

            'valid_id_type' => [
                'required',
                'string',
                Rule::in([
                    'National ID',
                    'Passport',
                    "Driver License",
                    'UMID',
                    'SSS ID',
                    'PhilHealth ID',
                    'Pag-IBIG Loyalty Card',
                    'Postal ID',
                    'PRC ID',
                    "Voter ID",
                    'Senior Citizen ID',
                    'PWD ID',
                    'School ID',
                    'Company ID',
                    'Barangay ID',
                    'National Police Clearance',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | VALID ID NUMBER
            |--------------------------------------------------------------------------
            */

            'valid_id_number' => [
                'required',
                'string',
                'max:50',
                "unique:users,valid_id_number,{$userId}",
            ],

            /*
            |--------------------------------------------------------------------------
            | FRONT VALID ID
            |--------------------------------------------------------------------------
            */

            'front_valid_id_picture' => $this->hasFile(
                'front_valid_id_picture'
            )
                ? 'required|image|max:10240'
                : 'nullable',

            /*
            |--------------------------------------------------------------------------
            | BACK VALID ID
            |--------------------------------------------------------------------------
            */

            'back_valid_id_picture' => $this->hasFile(
                'back_valid_id_picture'
            )
                ? 'required|image|max:10240'
                : 'nullable',

            /*
            |--------------------------------------------------------------------------
            | ADDRESS
            |--------------------------------------------------------------------------
            */

            'street' => [
                'required',
                'string',
            ],

            'postal_code' => [
                'required',
                'string',
                'max:20',
            ],
        ];
    }
}
