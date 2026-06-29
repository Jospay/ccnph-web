<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Store\ProductCardResource;
use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Http\Request;

class CollectionProductController extends Controller
{
    /**
     * Display all active collections of the authenticated user.
     */
    public function index(Request $request)
    {
        $collections = ProductCollection::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with([
                'product' => function ($query) use ($request) {
                    $query->with([
                        'images:id,product_id,image,sort_order',
                        'defaultVariant:id,product_id,price,compare_price',
                    ])
                        ->withSum('variants as total_stock', 'stock')
                        ->withExists([
                            'collections as is_liked' => function ($q) use ($request) {
                                $q->where('user_id', $request->user()->id)
                                    ->where('is_active', true);
                            },
                        ]);
                },
            ])
            ->latest()
            ->get();

        // TRANSFORM HERE: Format the nested elements explicitly into a clean JSON array
        $transformedCollections = $collections->map(function ($collection) use ($request) {
            return [
                'id' => $collection->id,
                'user_id' => $collection->user_id,
                'product_id' => $collection->product_id,
                'is_active' => (bool) $collection->is_active,
                'created_at' => $collection->created_at?->toIso8601String(),
                'updated_at' => $collection->updated_at?->toIso8601String(),
                // This forces Laravel to resolve your ProductCardResource array immediately!
                'product' => $collection->product
                    ? (new ProductCardResource($collection->product))->toArray($request)
                    : null,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'collections' => $transformedCollections,
        ]);
    }

    /**
     * Toggle the favorite collection state via product slug route binding.
     */
    public function toggle(Request $request, Product $product)
    {
        $collection = ProductCollection::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        if (! $collection->exists) {
            $collection->is_active = true;
            $collection->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Added to collection.',
                'is_active' => true,
            ]);
        }

        $collection->update([
            'is_active' => ! $collection->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $collection->is_active
                ? 'Added to collection.'
                : 'Removed from collection.',
            'is_active' => $collection->is_active,
        ]);
    }
}
