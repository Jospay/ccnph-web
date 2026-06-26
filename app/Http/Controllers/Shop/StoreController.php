<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Http\Resources\Shop\ProductCardResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function show(Store $store)
    {
        return Inertia::render('shop/public/store/Show', [
            'store' => $store,
            'products' => ProductCardResource::collection(
                $this->buildBaseQuery($store)
                ->paginate(20)
                ->withQueryString()
            ),
        ]);
    }

    private function buildBaseQuery(Store $store)
    {
        return $store->products()
        ->select([
            'id',
            'store_id',
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
        ->whereHas('variants', fn ($q) =>
            $q->where('stock', '>', 0)
        )
        ->where('is_active', true)
        ->latest();
    }
}
