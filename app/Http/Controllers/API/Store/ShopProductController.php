<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\ProductCardResource;
use App\Http\Resources\Api\Store\ProductShowResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(['top-deals', 'discover'])],
        ]);

        $filters = [
            'type' => $validated['type'] ?? 'top-deals',
        ];

        $products = $this->buildBaseQuery($filters)->paginate(20)->withQueryString();

        return response()->json([
            'status' => 'success',
            'products' => ProductCardResource::collection($products)->response()->getData(true),
            'filters' => $filters,
        ]);
    }

    private function buildBaseQuery(array $filters): Builder
    {
        return Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'is_featured',
                'created_at',
            ])
            ->with([
                'images:id,product_id,image,sort_order',
                'defaultVariant:id,product_id,price,compare_price',
            ])
            ->whereHas('variants', function (Builder $query) {
                $query->where('stock', '>', 0);
            })
            ->withSum('variants as total_stock', 'stock')
            ->where('is_active', true)
            ->when(
                $filters['type'] === 'top-deals',
                fn (Builder $query) => $query->where('is_featured', true)->latest(),
                fn (Builder $query) => $query->latest()
            );
    }

    public function show(Product $product)
    {
        $product->load([
            'store',
            'categories',
            'images',
            'variants.attributeValues.attribute',
        ]);

        return response()->json([
            'status' => 'success',
            'product' => ProductShowResource::make($product)->resolve(),
        ]);
    }
}
