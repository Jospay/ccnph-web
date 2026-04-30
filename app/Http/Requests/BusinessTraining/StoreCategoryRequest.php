<?php

namespace App\Http\Requests\BusinessTraining;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;

class StoreCategoryRequest extends FormRequest
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
        return [       
            'name' => 'required|string|max:255|unique:business_training_categories,name',
            'description' => 'required|string|max:1000',
            
            'modules' => 'required|array|size:6',

            // MODULE 1
            'modules.0.intro_title' => 'required|string|max:255',
            'modules.0.intro_description' => 'required|string|max:1000',

            'modules.0.advantages' => 'array|max:10',
            'modules.0.challenges' => 'array|max:10',
            'modules.0.advantages.*' => 'required|string|max:255',
            'modules.0.challenges.*' => 'required|string|max:255',

            'modules.0.required_mindset' => 'array|max:10',
            'modules.0.required_mindset.*.name' => 'required|string|max:255',
            'modules.0.required_mindset.*.description' => 'required|string|max:1000',

            // MODULE 2,4,5,6
            'modules.1.items' => 'array|max:5',
            'modules.3.items' => 'array|max:5',
            'modules.4.items' => 'array|max:5',
            'modules.5.items' => 'array|max:5',
            'modules.*.items.*.title' => 'required|string|max:255',
            'modules.*.items.*.description' => 'required|string|max:1000',

            // MODULE 3
            'modules.2.budget' => 'array|max:10',
            'modules.2.budget.*.item' => 'required|string|max:255',
            'modules.2.budget.*.min_cost' => 'required|numeric|min:1|max:1000000000',
            'modules.2.budget.*.max_cost' => 'required|numeric|min:1|max:1000000000',

            'modules.2.min_cost' => 'required|numeric|min:1|max:1000000000',
            'modules.2.max_cost' => 'required|numeric|min:1|max:1000000000',
        ];
    }
}
