<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Status;
use App\Models\UserType;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (
            $user && 
            $user->status_id === Status::ACTIVE && 
            $user->user_type_id === UserType::MEMBER && 
            $user->is_seller === true
        ) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Active seller account required.');
    }
}
