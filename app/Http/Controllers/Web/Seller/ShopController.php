<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\Shop\StoreShopRequest;
use App\Http\Requests\Seller\Shop\UpdateShopRequest;
use Illuminate\Http\Request;
use App\Models\Shop;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user()->loadMissing(['shop']);
        if ($user->shop) {
            abort(403, 'You already have a shop.');
        }

        return Inertia::render('seller/shop/Create');
    }

    public function store(StoreShopRequest $request)
    {
        $validated = $request->validated();

        Shop::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()->route('seller.dashboard.index')
            ->with('success', 'Congratulations! Your shop is now open.');
    }
    
    public function edit(Shop $shop)
    {
        if ($shop->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('seller/shop/Edit', [
            'shop' => $shop
        ]);
    }

    public function update(UpdateShopRequest $request, Shop $shop)
    {
        $validated = $request->validated();

        $shop->description = $validated['description'];

        if ($request->hasFile('logo')) {
            $shop->logo = $request->file('logo')->store('shops/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $shop->banner = $request->file('banner')->store('shops/banners', 'public');
        }

        $shop->save();

        return back()
            ->with('success', 'Shop profile updated successfully!');
    }
}