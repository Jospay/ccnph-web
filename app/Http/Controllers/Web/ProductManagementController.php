<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\CooperativeNationalStatus;
use App\Http\Resources\ProductManagementIndexResource;
use App\Models\Cooperative;
use App\Models\UserType;
use App\Models\Shop;
use App\Models\Product;

class ProductManagementController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'c' => ['sometimes', 'nullable', 'string', 'exists:cooperatives,slug'],
            's' => ['sometimes', 'nullable', Rule::in(['active', 'inactive'])],
            'ns' => ['sometimes', 'nullable', Rule::enum(CooperativeNationalStatus::class)],
        ]);

        $filters = [
            'q' => trim($validated['q'] ?? ''),
            'c' => $validated['c'] ?? 'all',
            's' => $validated['s'] ?? 'all',
            'ns' => $validated['ns'] ?? 'all',
        ];

        $cooperativeId = $filters['c'] !== 'all'
            ? Cooperative::where('slug', $filters['c'])->value('id')
            : null;

        $products = Product::query()
            ->with([
                'shop:id,name,cooperative_id',
                'shop.cooperative:id,name',
                'thumbnail',
                'defaultVariant:id,product_id,price,compare_price',
            ])
            ->withSum('variants as total_stock', 'stock')
            ->when(
                $filters['q'] !== '',
                fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'),
            )
            ->when(
                $cooperativeId,
                fn ($query, $id) => $query->whereIn(
                    'shop_id',
                    Shop::select('id')->where('cooperative_id', $id),
                ),
            )
            ->when(
                $filters['s'] !== 'all',
                fn ($query) => $query->where('is_active', $filters['s'] === 'active'),
            )
            ->when(
                $filters['ns'] !== 'all',
                fn ($query) => $query->where('national_status', $filters['ns']),
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('online-store-management/ProductIndex', [
            'products' => $products->through(
                fn ($product) => (new ProductManagementIndexResource($product))->resolve()
            ),
            'cooperatives' => Cooperative::query()
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get(),
            'national_statuses' => collect(CooperativeNationalStatus::cases())
                ->map(fn ($case) => ['value' => $case->value])
                ->values(),
            'filters' => $filters,
            'can_mutate' => $this->canMutate(),
        ]);
    }
}
