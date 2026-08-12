<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'gender' => 'required|in:Male,Female,Other,Prefer not to say',
            'birthdate' => 'required|date|before:today',
            'email' => "required|email|unique:users,email,{$userId}",
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'barangay' => 'required|string',

            'valid_id_type' => 'required|in:National ID,Passport,Driver License',
            'valid_id_number' => "required|string|max:20|unique:users,valid_id_number,{$userId}",
            'front_valid_id_picture' => $this->hasFile('front_valid_id_picture') ? 'required|image|max:10240' : 'nullable',
            'back_valid_id_picture' => $this->hasFile('back_valid_id_picture') ? 'required|image|max:10240' : 'nullable',
            'street' => 'required|string',
            'postal_code' => 'required|string|max:20',
        ];
    }
}
