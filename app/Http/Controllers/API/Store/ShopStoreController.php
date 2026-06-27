<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\ProductCardResource;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ShopStoreController extends Controller
{
    /**
     * Display the specified store with its products.
     */
    public function show(Shop $store): JsonResponse
    {
        $products = $this->buildBaseQuery($store)->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Store retrieved successfully.',
            'data' => [
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'logo' => $store->logo,
                    'banner' => $store->banner,
                    'description' => $store->description,
                    'created_at' => $store->created_at,
                ],
                'products' => ProductCardResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ],
            ],
        ]);
    }

    /**
     * Base query for retrieving store products.
     */
    private function buildBaseQuery(Shop $store)
    {
        return $store->products()
            ->select([
                'id',
                'shop_id',
                'name',
                'slug',
                'is_featured',
                'is_active',
                'created_at',
            ])
            ->with([
                'images:id,product_id,image,sort_order',
                'defaultVariant:id,product_id,price,compare_price',
            ])
            ->withSum('variants as total_stock', 'stock')
            ->whereHas('variants', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->where('is_active', true)
            ->latest();
    }
}
