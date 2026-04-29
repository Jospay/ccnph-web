<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Http\Resources\MemberUserResource;
use App\Http\Resources\MemberUserDetailResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Inertia\Response;
use Inertia\Inertia;

class CoopMembershipController extends Controller
{
    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['for_approval', 'approved', 'active'])],
            'type' => ['sometimes', 'string', Rule::in(['basic', 'member'])],
        ]);

        // Determine type & enforce allowed statuses
        $type = $validated['type'] ?? 'basic';
        $allowedStatuses = $type === 'basic'
            ? ['for_approval', 'approved', 'active']
            : ['active'];

        // Resolve status safely
        $status = $validated['status'] ?? null;
        if (!$status || !in_array($status, $allowedStatuses)) {
            $status = $type === 'basic' ? 'for_approval' : 'active';
        }

        // Set defaults
        $filters = [
            'type' => $type,
            'status' => $status,
        ];
        $memberUsers = $this->buildBaseQuery($filters)->get();

        return Inertia::render('coop-membership/Index', [
            'member_users' => MemberUserResource::collection($memberUsers),
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
            'userType:id,name',
        ])
        ->whereHas('status', fn($q) => $q->where('name', $filters['status']))
        ->whereHas('userType', fn($q) => $q->where('name', $filters['type']));

        return $query;
    }

    public function show(User $user)
    {
        $user->loadMissing([
            'status:id,name',
            'userType:id,name',
        ]);

        return MemberUserDetailResource::make($user);
    }

    public function updateStatus(User $user, Request $request)
    {
        $action = $request->input('action');

        if ($action === 'approve') {
            $user->update([
                'user_type_id' => UserType::MEMBER,
                'status_id' => Status::ACTIVE,
            ]);
        }

        if ($action === 'decline') {
            $user->update([
                'status_id' => Status::ACTIVE,
            ]);
        }   

        return back();
    }
}
