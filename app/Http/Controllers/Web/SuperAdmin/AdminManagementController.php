<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\AdminUserDetailResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Inertia\Response;
use Inertia\Inertia;

class AdminManagementController extends Controller
{
    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['active'])],
        ]);
        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'active',
        ];
        $adminUsers = $this->buildBaseQuery($filters)->get();

        return Inertia::render('admin-management/Index', [
            'admin_users' => AdminUserResource::collection($adminUsers),
            'filters' => $filters
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = User::with([
            'status:id,name',
            'services:id,name',
        ])
        ->where('user_type_id', UserType::ADMIN)
        ->whereHas('status', fn($q) => $q->where('name', $filters['status']));

        return $query;
    }

    public function show(User $user)
    {
        $user->loadMissing([
            'status:id,name',
            'services:id,name',
        ]);

        return AdminUserDetailResource::make($user);
    }
}
