<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\UserAddressResource;
use App\Http\Requests\Shop\UserAddressRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Enums\UserAddressLabel;
use App\Models\UserAddress;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('addresses');

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return Inertia::render('shop/customer/account/address/Index', [
            'user' => $user->only('name', 'phone', 'avatar'),
            'addresses' => UserAddressResource::collection($addresses)->resolve(),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        return Inertia::render('shop/customer/account/address/Create', [
            'user' => $user->only('name', 'phone', 'avatar'),
        ]);
    }

    public function store(UserAddressRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($user, $validated) {

            $isFirstAddress = !$user->addresses()->exists();

            $user->addresses()->create([
                ...$validated,
                'is_default' => $isFirstAddress ? true : false
            ]);
        });

        return redirect()->route('shop.account.addresses.index')
            ->with('success', 'New address added successfully.');
    }
 
    public function update(UserAddressRequest $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
 
        $validated = $request->validated();
        $user      = $request->user();
 
        DB::transaction(function () use ($user, $address, $validated) {
            if (! empty($validated['is_default'])) {
                // Promote this address to default — demote all others
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
 
                $validated['is_default'] = true;
            } else {
                // Demote this address
                unset($validated['is_default']);
            }
 
            $address->update($validated);
        });
 
        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        DB::transaction(function () use ($address) {
            $user = $address->user;
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $nextAddress = $user
                    ->addresses()
                    ->oldest('id')
                    ->first();

                if ($nextAddress) {
                    $nextAddress->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return back()->with('success', 'Address removed successfully.');
    }
}
