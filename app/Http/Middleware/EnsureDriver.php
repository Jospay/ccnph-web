<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        $driver = $request->user();

        if (! $driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Driver account required.'], 403);
        }

        if (! $driver->is_active) {
            $driver->tokens()->delete();

            return response()->json(['success' => false, 'message' => 'Your driver account is deactivated.'], 403);
        }

        return $next($request);
    }
}
