<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Status;
use Illuminate\Validation\ValidationException;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|int ...$roles  <-- This captures the parameters
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        // Check if the user's type ID is in the allowed roles array
        if ($user && $user->status_id === Status::ACTIVE && in_array($user->user_type_id, $roles)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this page');
    }
}