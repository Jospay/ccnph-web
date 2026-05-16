<?php

namespace App\Http\Requests\IntellectualProperty;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Retrieve the model from the route
        $property = $this->route('property');
        
        $requiresPaymentFields =
            $property &&
            $this->input('action') === 'approve' &&
            $property->form_type === 'payment';

        return [
            'action' => ['required', Rule::in(['approve', 'decline'])],
            'amount' => [
                Rule::requiredIf($requiresPaymentFields),
                'nullable',
                'numeric',
                'min:1',
                'max:1000000000',
            ],
            'allowed_term_months' => [
                Rule::requiredIf($requiresPaymentFields),
                'array',
                'min:1',
            ],
            'allowed_term_months.*' => [
                'integer',
                Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
            ],
        ];
    }
}
