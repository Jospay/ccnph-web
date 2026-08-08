<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Service;
use App\Models\User;
use App\Models\UserType;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing('userType');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email']),
                'userType' => $user?->userType?->name,
                'is_seller' => (bool) ($user?->is_seller ?? false),
                'managed_services' => $user ? $this->getManagedServices($user) : [],
                'unread_notifications_count' => $user?->unreadNotifications()->count() ?? 0,
            ],
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }

    // sidebar purposes
    protected function getManagedServices(User $user)
    {
        // Super Admin sees all active services
        if ($user->user_type_id === UserType::SUPER_ADMIN) {
            return Service::where('is_active', true)->get(['id', 'name', 'slug', 'icon']);
        }

        // Admin sees only assigned active services
        if ($user->user_type_id === UserType::ADMIN) {
            return $user->services()
                ->where('services.is_active', true)
                ->get(['services.id', 'services.name', 'slug', 'icon']);
        }

        return [];
    }
}
