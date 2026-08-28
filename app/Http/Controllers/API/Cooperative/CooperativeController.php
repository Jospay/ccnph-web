<?php

namespace App\Http\Controllers\API\Cooperative;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CooperativeController extends Controller
{
   public function index(): JsonResponse
    {
        $cooperatives = DB::table('cooperatives')
            ->select('id', 'name', 'primary_color', 'secondary_color', 'logo')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($coop) {
                $coop->logo = $coop->logo ? asset('storage/' . $coop->logo) : null;
                return $coop;
            });

        return response()->json($cooperatives);
    }
}
