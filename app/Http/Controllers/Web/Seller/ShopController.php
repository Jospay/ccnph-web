<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function create()
        {
            return Inertia::render('seller/shop/Create'); 
        }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $validated['slug'] = Str::slug($validated['name']);

    $request->user()->shop()->create($validated);

    return redirect()->route('seller.dashboard.index')
        ->with('success', 'Your storefront has been successfully created!');
}
}
