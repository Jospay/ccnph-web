<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\ProductCardResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('shop/public/Home', [
            'productsTopDeals' => ProductCardResource::collection(
                $this->buildBaseQuery('top_deals')
                    ->limit(4)
                    ->get()
            ),

            'productsDiscover' => ProductCardResource::collection(
                $this->buildBaseQuery('discover')
                    ->limit(10)
                    ->get()
            ),
        ]);
    }

    private function buildBaseQuery(string $type): Builder
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
            ->withSum('variants as total_stock', 'stock')
            ->having('total_stock', '>', 0)
            ->where('is_active', true)
            ->when(
                $type === 'top_deals',
                fn (Builder $query) => $query
                    ->where('is_featured', true)
                    ->latest(),

                fn (Builder $query) => $query
                    ->latest()
            );
    }
}