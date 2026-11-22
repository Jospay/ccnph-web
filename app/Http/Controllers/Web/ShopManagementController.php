<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Cooperative;
use App\Models\UserType;
use App\Models\Shop;
use App\Http\Resources\ShopManagementIndexResource;
use App\Http\Resources\ShopManagementShowResource;

class ShopManagementController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            's' => ['sometimes', 'string', Rule::in(['all', 'active', 'inactive'])],
            'c' => [
                'sometimes',
                'string',
                Rule::when(
                    $request->input('c') !== 'all',
                    [Rule::exists('cooperatives', 'slug')],
                ),
            ],
        ]);

        $filters = [
            'status' => $validated['s'] ?? 'all',
            'cooperative_slug' => $validated['c'] ?? 'all',
        ];

        $shops = Shop::query()
            ->with([
                'cooperative:id,name,slug',
                'user:id,name',
            ])
            ->when(
                $filters['status'] !== 'all',
                fn ($query) => $query->where(
                    'is_active',
                    $filters['status'] === 'active',
                ),
            )
            ->when(
                $filters['cooperative_slug'] !== 'all',
                fn ($query) => $query->whereHas(
                    'cooperative',
                    fn ($cooperativeQuery) => $cooperativeQuery->where(
                        'slug',
                        $filters['cooperative_slug'],
                    ),
                ),
            )
            ->latest()
            ->get();

        return Inertia::render('online-store-management/ShopIndex', [
            'shops' => ShopManagementIndexResource::collection($shops)->resolve(),
            'cooperatives' => Cooperative::query()
                ->select('slug', 'name')
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
            'can_mutate' => $this->canMutate(),
        ]);
    }

    public function show(Shop $shop): ShopManagementShowResource
    {
        return new ShopManagementShowResource(
            $shop->load([
                'cooperative:id,name,logo',
                'user:id,name,email,phone,region,province,city,barangay,street,postal_code',
            ])
        );
    }
}
