<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\CooperativeScope;
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
        // Fetch structural parent item nodes
        $categories = Category::select(['id', 'name', 'slug'])
            ->whereNull('parent_id')
            ->get();

        $productsTopDeals = ProductCardResource::collection(
            $this->buildBaseQuery('top_deals', $request)
                ->limit(6)
                ->get()
        );

        // Standardized paginated output matrices
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

    public function topDeals(Request $request): JsonResponse
    {
        $pagination = $this->buildBaseQuery('top_deals', $request)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductCardResource::collection($pagination->items()),
                'pagination' => [
                    'current_page' => $pagination->currentPage(),
                    'last_page' => $pagination->lastPage(),
                    'has_more' => $pagination->hasMorePages(),
                ],
            ],
        ]);
    }

    private function buildBaseQuery(string $type, Request $request): Builder
    {
        $user = $request->user();
        $userId = $user?->id;
        $userCoopId = $user?->cooperative_id;
        $scope = $request->query('scope'); // all | local | national

        return Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.rating',
                'products.sold_count',
                'products.is_featured',
                'products.cooperative_id',
                'products.cooperative_scope',
                'products.created_at',
            ])
            ->withSum('variants as total_stock', 'stock')
            ->with([
                'images:id,product_id,image,sort_order',
                'defaultVariant:id,product_id,price,compare_price',
            ])
            ->where('products.is_active', true)
            ->whereHas('variants', function (Builder $query) {
                $query->where('stock', '>', 0);
            })

            // VISIBILITY RULE: national = everyone, local = same cooperative only
            ->where(function (Builder $q) use ($userCoopId) {
                $q->where('products.cooperative_scope', CooperativeScope::NATIONAL->value);

                if ($userCoopId) {
                    $q->orWhere(function (Builder $local) use ($userCoopId) {
                        $local->where('products.cooperative_scope', CooperativeScope::LOCAL->value)
                            ->where('products.cooperative_id', $userCoopId);
                    });
                }
            })

            // OPTIONAL FILTER FROM THE APP: local / national
            ->when(
                in_array($scope, [CooperativeScope::LOCAL->value, CooperativeScope::NATIONAL->value], true),
                fn (Builder $query) => $query->where('products.cooperative_scope', $scope)
            )

            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $query->where('products.name', 'like', '%'.$request->query('search').'%');
            })
            ->when($userId, function (Builder $query) use ($userId) {
                $query->withExists(['collections as is_liked' => function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->where('is_active', true);
                }]);
            })
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
