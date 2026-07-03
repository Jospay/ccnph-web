<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\ProductCardResource;
use App\Models\Shop;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
                    'logo' => $store->logo ? asset('storage/'.$store->logo) : null,
                    'banner' => $store->banner ? asset('storage/'.$store->banner) : null,
                    'description' => $store->description,
                    'rating' => $store->rating,
                    'is_official' => (bool) $store->is_official,
                    'created_at' => $store->created_at,
                ],
                'products' => ProductCardResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'has_more' => $products->hasMorePages(),
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
                'rating',
                'sold_count',
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

    public function registerSeller(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_seller || Shop::where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a seller.',
            ], 400);
        }

        if ($user->user_type_id === UserType::BASIC) {
            $user->update(['user_type_id' => UserType::MEMBER]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shops', 'name')],
            'description' => ['required', 'string', 'max:1000'],
            'logo' => ['required', 'image', 'max:4096'],
            'banner' => ['required', 'image', 'max:6144'],
        ]);

        try {
            return DB::transaction(function () use ($validated, $user, $request) {
                // 1. Create and populate the Shop instance
                $shop = new Shop;
                $shop->user_id = $user->id;
                $shop->name = $validated['name'];
                $shop->slug = Str::slug($validated['name']);
                $shop->description = $validated['description'];
                $shop->is_active = true;
                $shop->is_official = false;
                $shop->rating = 5.0;

                // Process Logo file upload
                if ($request->hasFile('logo')) {
                    $logoPath = $request->file('logo')->store('shops/logos', 'public');
                    $shop->logo = $logoPath;
                }

                // Process Banner file upload
                if ($request->hasFile('banner')) {
                    $bannerPath = $request->file('banner')->store('shops/banners', 'public');
                    $shop->banner = $bannerPath;
                }

                $shop->save();

                // 2. ✅ Update the users table column parameter cleanly
                $user->update(['is_seller' => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Seller shop profile registered successfully.',
                    'data' => [
                        'id' => $shop->id,
                        'name' => $shop->name,
                        'slug' => $shop->slug,
                    ],
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration layout processing failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
