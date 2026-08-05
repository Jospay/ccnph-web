<?php

namespace App\Http\Requests\Seller\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user    = $this->user()->loadMissing(['shop']);
        $product = $this->route('product');

        return $user->shop && $product->shop_id === $user->shop->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // core
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],

            // categories
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id', 'distinct'],

            // image management
            'deleted_image_ids'   => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer', 'exists:product_images,id'],
            'images'              => ['nullable', 'array'],
            'images.*'            => ['image', 'max:2048'],

            // video management
            'delete_video' => ['nullable', 'boolean'],
            'video'        => [
                'nullable', 'file',
                'mimetypes:video/mp4,video/webm,video/quicktime',
                'max:20480',
            ],

            // variant management
            'deleted_variant_ids'   => ['nullable', 'array'],
            'deleted_variant_ids.*' => ['integer', 'exists:product_variants,id'],

            'variants'                              => ['required', 'array', 'min:1'],
            'variants.*.id'                         => ['nullable', 'integer', 'exists:product_variants,id'],
            // SKU unique check is done in withValidator (needs per-row exclusion)
            'variants.*.sku'                        => ['required', 'string', 'max:255', 'distinct'],
            'variants.*.price'                      => ['required', 'numeric', 'min:0'],
            'variants.*.compare_price'              => ['nullable', 'numeric'],
            'variants.*.stock'                      => ['required', 'integer', 'min:0'],
            'variants.*.weight'                     => ['nullable', 'numeric', 'min:0'],
            'variants.*.image'                      => ['nullable', 'image', 'max:2048'],
            'variants.*.delete_image'               => ['nullable', 'boolean'],
            'variants.*.is_default'                 => ['required', 'boolean'],
            'variants.*.attributes'                 => ['required', 'array', 'min:1'],
            'variants.*.attributes.*.attribute_id'  => ['required', 'integer', 'exists:attributes,id'],
            'variants.*.attributes.*.value'         => ['required', 'string', 'max:255'],
            'variants.*.attributes.*.value_id'      => ['nullable', 'integer', 'exists:attribute_values,id'],
            'variants.*.attributes.*.is_new'        => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $variants = $this->input('variants', []);

            // ── SKU uniqueness (exclude own variant row) ──────────────────────
            foreach ($variants as $index => $variant) {
                $variantId = $variant['id'] ?? null;

                $exists = DB::table('product_variants')
                    ->where('sku', $variant['sku'])
                    ->when($variantId, fn ($q) => $q->where('id', '!=', $variantId))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        "variants.$index.sku",
                        'SKU already exists.'
                    );
                }
            }

            // ── only one default ──────────────────────────────────────────────
            if (collect($variants)->where('is_default', true)->count() > 1) {
                $validator->errors()->add('variants', 'Only one default variant is allowed.');
            }

            // ── no duplicate attributes within a variant ──────────────────────
            foreach ($variants as $index => $variant) {
                $ids = collect($variant['attributes'] ?? [])->pluck('attribute_id');
                if ($ids->duplicates()->isNotEmpty()) {
                    $validator->errors()->add(
                        "variants.$index.attributes",
                        'Duplicate attributes are not allowed within the same variant.'
                    );
                }
            }

            // ── no duplicate attribute combinations ───────────────────────────
            $signatures = collect($variants)->map(fn ($variant) =>
                collect($variant['attributes'] ?? [])
                    ->sortBy('attribute_id')
                    ->map(fn ($a) => $a['attribute_id'] . '=' . Str::lower(trim($a['value'])))
                    ->implode('|')
            );
            if ($signatures->duplicates()->isNotEmpty()) {
                $validator->errors()->add('variants', 'Duplicate variant combinations are not allowed.');
            }

            // ── compare price must be ≥ price ─────────────────────────────────
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

            // ── deleted variants belong to this product ───────────────────────
            $product           = $this->route('product');
            $deletedVariantIds = $this->input('deleted_variant_ids', []);

            if (!empty($deletedVariantIds)) {
                $valid = $product->variants()->whereIn('id', $deletedVariantIds)->pluck('id');
                $invalid = collect($deletedVariantIds)->diff($valid);
                if ($invalid->isNotEmpty()) {
                    $validator->errors()->add('deleted_variant_ids', 'Invalid variant IDs provided.');
                }
            }

            // ── deleted images belong to this product ─────────────────────────
            $deletedImageIds = $this->input('deleted_image_ids', []);
            if (!empty($deletedImageIds)) {
                $valid = $product->images()->whereIn('id', $deletedImageIds)->pluck('id');
                $invalid = collect($deletedImageIds)->diff($valid);
                if ($invalid->isNotEmpty()) {
                    $validator->errors()->add('deleted_image_ids', 'Invalid image IDs provided.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'variants.required'              => 'At least one variant is required.',
            'variants.*.attributes.required' => 'Variant must contain at least one attribute.',
        ];
    }
}