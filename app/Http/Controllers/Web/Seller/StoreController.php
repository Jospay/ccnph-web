<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreCreateRequest;
use App\Http\Requests\Seller\StoreUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user()->loadMissing(['store']);
        if ($user->store) {
            abort(403, 'You already have a store.');
        }

        return Inertia::render('seller/store/Create');
    }

    public function store(StoreCreateRequest $request)
    {
        $validated = $request->validated();

        Store::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()->route('seller.dashboard')
        ->with('success', 'Congratulations! Your store is now open.');
    }
    
    public function edit(Store $store)
    {
        if ($store->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('seller/store/Edit', [
            'store' => $store
        ]);
    }

    public function update(StoreUpdateRequest $request, Store $store)
    {
        $validated = $request->validated();

        $store->description = $validated['description'];

        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $store->banner = $request->file('banner')->store('stores/banners', 'public');
        }

        $store->save();

        return back()
        ->with('success', 'Store profile updated successfully!');
    }
}