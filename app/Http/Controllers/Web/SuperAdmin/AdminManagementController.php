<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminServiceRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Models\Service;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\AdminUserDetailResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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

        // for creation form
        $services = Service::where('is_super_admin_only', false)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return Inertia::render('admin-management/Index', [
            'admin_users' => AdminUserResource::collection($adminUsers),
            'filters' => $filters,
            'services' => $services
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

    public function store(StoreAdminRequest $request)
    {
        $validated = $request->validated();

        // Create the admin user & sync services
        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'user_type_id' => UserType::ADMIN,
                'status_id' => Status::ACTIVE,
            ]);
            $user->services()->sync($validated['service']);
        });
        
        return back();
    }

    public function updateServices(UpdateAdminServiceRequest $request, User $user)
    {
        $validated = $request->validated();
        abort_if($user->user_type_id !== UserType::ADMIN, 403);

        $user->services()->sync($validated['service_ids']);

        return back();
    }
}
