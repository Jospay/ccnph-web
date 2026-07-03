<?php

namespace App\Actions\Fortify;

use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        $url = match ($user->user_type_id) {
            UserType::SUPER_ADMIN, UserType::ADMIN => route('dashboard.index'),
            UserType::MEMBER => route('seller.dashboard.index'),
            default => route('home'),
        };

        return redirect()->to($url);
    }
}