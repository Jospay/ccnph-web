<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\ProductCardResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopHomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Fetch all parent categories from DB
        $categories = Category::select(['id', 'name', 'slug'])
            ->whereNull('parent_id')
            ->get();

        $productsTopDeals = ProductCardResource::collection(
            $this->buildBaseQuery('top_deals', $request)
                ->limit(4)
                ->get()
        );

        // Paginate Discover products: 10 items per page
        $discoverPagination = $this->buildBaseQuery('discover', $request)->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'productsTopDeals' => $productsTopDeals,
                'productsDiscover' => ProductCardResource::collection($discoverPagination->items()),
                'pagination' => [
                    'current_page' => $discoverPagination->currentPage(),
                    'last_page' => $discoverPagination->lastPage(),
                    'has_more' => $discoverPagination->hasMorePages(),
                ],
            ],
        ]);
    }

    private function buildBaseQuery(string $type, Request $request): Builder
    {
        return Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.is_featured',
                'products.created_at',
            ])
            ->with([
                'images:id,product_id,image,sort_order',
                'defaultVariant:id,product_id,price,compare_price',
            ])
            ->withSum('variants as total_stock', 'stock')
            ->having('total_stock', '>', 0)
            ->where('products.is_active', true)

            // Dynamic category filter based on request query params
            ->when(
                $type === 'discover' && $request->filled('category_id') && $request->query('category_id') !== 'All',
                fn (Builder $query) => $query->whereHas('categories', function (Builder $q) use ($request) {
                    $q->where('categories.id', $request->query('category_id'));
                })
            )
            ->when(
                $type === 'top_deals',
                fn (Builder $query) => $query->where('products.is_featured', true)->latest(),
                fn (Builder $query) => $query->latest()
            );
    }
}
