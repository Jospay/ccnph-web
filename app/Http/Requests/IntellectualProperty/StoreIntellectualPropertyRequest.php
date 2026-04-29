<?php

namespace App\Http\Requests\IntellectualProperty;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIntellectualPropertyRequest extends FormRequest
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
        return [
            'creation_type' => ['required', 'in:business_idea,invention'],
            'form_type' => ['required', 'in:grant,payment'],

            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'applicability' => ['required', 'string'],

            'claims' => ['required', 'array', 'min:1'],
            'claims.*.description' => ['required', 'string'],

            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf,svg', 'max:10240'],

            'is_original' => ['required', 'boolean', 'accepted'],
            'agreed_terms' => ['required', 'boolean', 'accepted'],
            'agreed_privacy' => ['required', 'boolean', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'creation_type.required' => 'Please select a creation type.',
            'form_type.required' => 'Please select a form type.',
            'title.required' => 'Please provide a title for your intellectual property.',
            'description.required' => 'Please provide a description of your intellectual property.',
            'applicability.required' => 'Please explain the industrial applicability of your intellectual property.',
            'claims.required' => 'At least one claim is required.',
            'claims.min' => 'At least one claim is required.',
            'is_original.accepted' => 'You must declare that this invention is original.',
            'agreed_terms.accepted' => 'You must agree to the Terms & Conditions.',
            'agreed_privacy.accepted' => 'You must agree to the Data Privacy Policy.',
        ];
    }
}
