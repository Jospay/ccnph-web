<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\Member\ProductResource;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing(['store']);

        if (! $user->store) {
            return redirect()->route('member.store.create');
        }

        return Inertia::render('member/dashboard/Index', [
            'store' => $user->store
        ]);
    }
}
