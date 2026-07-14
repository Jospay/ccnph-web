<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\Seller\ProductResource;
use App\Http\Resources\Seller\ProductShowResource;
use App\Http\Resources\Seller\ProductEditResource;
use App\Http\Requests\Seller\ProductCreateRequest;
use App\Http\Requests\Seller\ProductUpdateRequest;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'out-of-stock'])],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'active',
        ];
        
        $user = $request->user()->loadMissing(['store']);

        if (! $user->store) {
            return redirect()->route('seller.store.create');
        }

        $products = $this->buildBaseQuery($user, $filters)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('seller/product/Index', [
            'store' => $user->store,
            'products' => ProductResource::collection($products),
            'counts' => $this->getSummaryCounts($user->store->id),
            'filters' => $filters
        ]);
        
    } 

    private function buildBaseQuery(User $user, array $filters): Builder
    {
        return Product::query()
            ->where('store_id', $user->store->id)
            ->with([
                'categories',
                'images',
                'variants.attributeValues.attribute',
            ])
            ->when(
                $filters['tab'] === 'active', 
                fn ($query) => $query->where('is_active', true)
                    ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
            )
            ->when(
                $filters['tab'] === 'inactive', 
                fn ($query) => $query->where('is_active', false)
            )
            ->when(
                $filters['tab'] === 'out-of-stock', 
                fn ($query) => $query->where('is_active', true)
                    ->whereHas('variants')
                    ->whereDoesntHave('variants', fn ($q) => $q->where('stock', '>', 0))
            )
            ->latest();
    }

    private function getSummaryCounts(int $storeId): array
    {
        $counts = Product::query()
            ->where('store_id', $storeId)
            ->selectRaw("
                SUM(CASE WHEN is_active = 1 AND EXISTS (
                    SELECT 1 FROM product_variants WHERE product_variants.product_id = products.id AND stock > 0
                ) THEN 1 ELSE 0 END) as active,
                
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                
                SUM(CASE WHEN is_active = 1 AND NOT EXISTS (
                    SELECT 1 FROM product_variants WHERE product_variants.product_id = products.id AND stock > 0
                ) AND EXISTS (
                    SELECT 1 FROM product_variants WHERE product_variants.product_id = products.id
                ) THEN 1 ELSE 0 END) as out_of_stock
            ")
            ->first();

        return [
            'active' => (int) ($counts->active ?? 0),
            'inactive' => (int) ($counts->inactive ?? 0),
            'out_of_stock' => (int) ($counts->out_of_stock ?? 0),
        ];
    }

    public function create(Request $request)
    {
        $user = $request->user()->loadMissing(['store']);
        if (!$user->store) {
            abort(403, 'You do not have a store.');
        }

        return Inertia::render('seller/product/Create', [
            'categories' => CategoryResource::collection(
                Category::query()
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get()
            )->resolve(),

            'attributes' => AttributeResource::collection(
                Attribute::with('values')->get()
            )->resolve(),
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $product->loadMissing([
            'store',
            'categories',
            'images',
            'variants.attributeValues.attribute',
        ]);

        abort_unless(
            $request->user()->id === $product->store->user_id,
            403
        );

        return ProductShowResource::make($product);
    }

    public function store(ProductCreateRequest $request)
    {
        $user = $request->user()->loadMissing('store');
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user, $request) {
            // product
            $product = Product::create([
                'store_id' => $user->store->id,
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug(
                    $validated['name']
                ),
                'description' => $validated['description'],
                'is_active' => true,
                'is_featured' => $validated['is_featured'],
            ]);

            // categories
            $product->categories()->sync(
                $validated['category_ids']
            );

            // product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store(
                        'products/images',
                        'public'
                    );

                    $product->images()->create([
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            // video
            if ($request->hasFile('video')) {
                $videoPath = $request
                    ->file('video')
                    ->store(
                        'products/videos',
                        'public'
                    );

                $product->update([
                    'video' => $videoPath,
                ]);
            }

            // variants
            foreach ($validated['variants'] as $variantData) {
                $variantImagePath = null;

                if (
                    isset($variantData['image'])
                    && $variantData['image']
                ) {
                    $variantImagePath =
                        $variantData['image']->store(
                            'products/variants',
                            'public'
                        );
                }

                $variant = $product
                    ->variants()
                    ->create([
                        'sku' => $variantData['sku'],
                        'is_default' => $variantData['is_default'],
                        'price' => $variantData['price'],
                        'compare_price' => $variantData['compare_price'],
                        'stock' => $variantData['stock'],
                        'weight' => $variantData['weight'],
                        'image' => $variantImagePath,
                    ]);

                // attributes
                $attributeValueIds = [];
                foreach ($variantData['attributes'] as $attributeData) {
                    $attribute = Attribute::findOrFail(
                        $attributeData['attribute_id']
                    );

                    // existing value
                    if (!empty($attributeData['value_id'])) {
                        $attributeValue =
                            AttributeValue::query()
                                ->where(
                                    'id',
                                    $attributeData['value_id']
                                )
                                ->where(
                                    'attribute_id',
                                    $attribute->id
                                )
                                ->first();

                        if (!$attributeValue) {
                            throw ValidationException::withMessages([
                                'variants' => [
                                    'Invalid attribute value selected.',
                                ],
                            ]);
                        }
                    }

                    // new value
                    else {
                        $attributeValue =
                            AttributeValue::firstOrCreate(
                                [
                                    'attribute_id' => $attribute->id,
                                    'value' => trim(
                                        $attributeData['value']
                                    ),
                                ]
                            );
                    }

                    $attributeValueIds[] = $attributeValue->id;
                }

                $variant
                    ->attributeValues()
                    ->sync($attributeValueIds);
            }
        });

        return redirect()->route('seller.dashboard')
        ->with('success', 'Product published successfully!');
    }

    public function edit(Request $request, Product $product)
    {
        $product->load([
            'categories',
            'images',
            'variants.attributeValues',
        ]);

        abort_unless(
            $product->store_id === $request->user()->store->id,
            403
        );

        return Inertia::render('seller/product/Edit', [
            'product' => ProductEditResource::make($product)->resolve(),
            'categories' => CategoryResource::collection(
                Category::query()
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get()
            )->resolve(),
            'attributes' => AttributeResource::collection(
                Attribute::with('values')->get()
            )->resolve(),
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $product, $request) {

            // ── core fields ──────────────────────────────────────────────────
            $product->update([
                'name'        => $validated['name'],
                'slug'        => $product->name !== $validated['name']
                    ? $this->generateUniqueSlug($validated['name'], $product->id)
                    : $product->slug,
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'],
                'is_featured' => $validated['is_featured'],
            ]);

            // ── categories ───────────────────────────────────────────────────
            $product->categories()->sync($validated['category_ids']);

            // ── delete removed images ─────────────────────────────────────────
            if (!empty($validated['deleted_image_ids'])) {
                $product->images()
                    ->whereIn('id', $validated['deleted_image_ids'])
                    ->get()
                    ->each(function ($img) {
                        // Storage::disk('public')->delete($img->image);
                        $img->delete();
                    });
            }

            // ── add new images ────────────────────────────────────────────────
            if ($request->hasFile('images')) {
                $nextOrder = ($product->images()->max('sort_order') ?? -1) + 1;

                foreach ($request->file('images') as $i => $file) {
                    $product->images()->create([
                        'image'      => $file->store('products/images', 'public'),
                        'sort_order' => $nextOrder + $i,
                    ]);
                }
            }

            // ── video ─────────────────────────────────────────────────────────
            if ($request->boolean('delete_video') && $product->video) {
                // Storage::disk('public')->delete($product->video);
                $product->update(['video' => null]);
            }

            if ($request->hasFile('video')) {
                if ($product->video) {
                    Storage::disk('public')->delete($product->video);
                }
                $product->update([
                    'video' => $request->file('video')->store('products/videos', 'public'),
                ]);
            }

            // ── delete removed variants ───────────────────────────────────────
            if (!empty($validated['deleted_variant_ids'])) {
                $product->variants()
                    ->whereIn('id', $validated['deleted_variant_ids'])
                    ->get()
                    ->each(function ($v) {
                        if ($v->image) Storage::disk('public')->delete($v->image);
                        $v->delete();
                    });
            }

            // ── upsert variants ───────────────────────────────────────────────
            foreach ($validated['variants'] as $index => $variantData) {
                $variantId  = $variantData['id'] ?? null;
                $variantFile = $request->file("variants.$index.image");

                if ($variantId) {
                    // update existing
                    $variant = $product->variants()->findOrFail($variantId);

                    if (!empty($variantData['delete_image']) && $variant->image) {
                        // Storage::disk('public')->delete($variant->image);
                        $variant->image = null;
                    }

                    if ($variantFile) {
                        // if ($variant->image) Storage::disk('public')->delete($variant->image);
                        $variant->image = $variantFile->store('products/variants', 'public');
                    }

                    $variant->update([
                        'sku'           => $variantData['sku'],
                        'is_default'    => $variantData['is_default'],
                        'price'         => $variantData['price'],
                        'compare_price' => $variantData['compare_price'] ?? null,
                        'stock'         => $variantData['stock'],
                        'weight'        => $variantData['weight'] ?? null,
                        'image'         => $variant->image,
                    ]);
                } else {
                    // create new
                    $imagePath = $variantFile
                        ? $variantFile->store('products/variants', 'public')
                        : null;

                    $variant = $product->variants()->create([
                        'sku'           => $variantData['sku'],
                        'is_default'    => $variantData['is_default'],
                        'price'         => $variantData['price'],
                        'compare_price' => $variantData['compare_price'] ?? null,
                        'stock'         => $variantData['stock'],
                        'weight'        => $variantData['weight'] ?? null,
                        'image'         => $imagePath,
                    ]);
                }

                // ── sync attributes ───────────────────────────────────────────
                $attributeValueIds = [];

                foreach ($variantData['attributes'] as $attr) {
                    $attribute = Attribute::findOrFail($attr['attribute_id']);

                    if (!empty($attr['value_id']) && empty($attr['is_new'])) {
                        $av = AttributeValue::query()
                            ->where('id', $attr['value_id'])
                            ->where('attribute_id', $attribute->id)
                            ->first();

                        if (!$av) {
                            throw ValidationException::withMessages([
                                'variants' => ['Invalid attribute value selected.'],
                            ]);
                        }
                    } else {
                        $av = AttributeValue::firstOrCreate([
                            'attribute_id' => $attribute->id,
                            'value'        => trim($attr['value']),
                        ]);
                    }

                    $attributeValueIds[] = $av->id;
                }

                $variant->attributeValues()->sync($attributeValueIds);
            }
        });

        return redirect()->route('seller.products.index')
            ->with('success', 'Product updated successfully!');
    }

    protected function generateUniqueSlug(string $name): string 
    {
        return Str::slug($name) . '-' . strtolower(Str::random(8));
    }
}
