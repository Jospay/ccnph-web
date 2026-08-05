<?php

namespace App\Http\Requests\Seller\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user()->loadMissing(['store']);
        return $user->store && $user->store->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // product
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
            'category_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'category_ids.*' => [
                'integer',
                'exists:categories,id',
                'distinct',
            ],
            'is_featured' => [
                'required',
                'boolean',
            ],
            // media
            'images' => [
                'required',
                'array',
                'min:1',
                'max:5',
            ],
            'images.*' => [
                'image',
                'max:2048',
            ],
            'video' => [
                'nullable',
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime',
                'max:20480',
            ],
            // variants
            'variants' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            'variants.*.sku' => [
                'required',
                'string',
                'max:255',
                'distinct',
                'unique:product_variants,sku',
            ],
            'variants.*.price' => [
                'required',
                'numeric',
                'min:1',
                'max:999999.99',
            ],
            'variants.*.compare_price' => [
                'required',
                'numeric',
                'min:1',
                'max:999999.99',
            ],
            'variants.*.stock' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'variants.*.weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9000.99',
            ],
            'variants.*.image' => [
                'required',
                'image',
                'max:2048',
            ],
            'variants.*.is_default' => [
                'required',
                'boolean',
            ],
            // attributes
            'variants.*.attributes' => [
                'required',
                'array',
                'min:1',
            ],
            'variants.*.attributes.*.attribute_id' => [
                'required',
                'integer',
                'exists:attributes,id',
            ],
            'variants.*.attributes.*.value' => [
                'required',
                'string',
                'max:255',
            ],
            'variants.*.attributes.*.value_id' => [
                'nullable',
                'integer',
                'exists:attribute_values,id',
            ],
            'variants.*.attributes.*.is_new' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Configure validator.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $variants = $this->input('variants', []);

            // only one default variant
            $defaultCount = collect($variants)
                ->where('is_default', true)
                ->count();
            if ($defaultCount > 1) {
                $validator->errors()->add(
                    'variants',
                    'Only one default variant is allowed.'
                );
            }

            // duplicate attribute in same variant
            foreach ($variants as $index => $variant) {
                $attributeIds = collect(
                    $variant['attributes'] ?? []
                )->pluck('attribute_id');
                if ($attributeIds->duplicates()->isNotEmpty()) {

                    $validator->errors()->add(
                        "variants.$index.attributes",
                        'Duplicate attributes are not allowed within the same variant.'
                    );
                }
            }

            // duplicate variant combinations
            $signatures = collect($variants)
                ->map(function ($variant) {
                    return collect(
                        $variant['attributes'] ?? []
                    )
                        ->sortBy('attribute_id')
                        ->map(function ($attribute) {
                            return
                                $attribute['attribute_id']
                                .'='.
                                Str::lower(
                                    trim($attribute['value'])
                                );
                        })
                        ->implode('|');
                });
            if ($signatures->duplicates()->isNotEmpty()) {
                $validator->errors()->add(
                    'variants',
                    'Duplicate variant combinations are not allowed.'
                );
            }

            // compare price must be higher
            foreach ($variants as $index => $variant) {

                if (
                    filled($variant['compare_price'] ?? null)
                    && $variant['compare_price'] < $variant['price']
                ) {

                    $validator->errors()->add(
                        "variants.$index.compare_price",
                        'Compare price must be greater than or equal to price.'
                    );
                }
            }
        });
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'variants.required' => 'At least one variant is required.',
            'variants.*.sku.unique' => 'SKU already exists.',
            'variants.*.attributes.required' =>
            'Variant must contain at least one attribute.',
        ];
    }
}