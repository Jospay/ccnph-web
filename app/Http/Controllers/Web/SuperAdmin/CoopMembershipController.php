<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberUserDetailResource;
use App\Http\Resources\MemberUserResource;
use App\Models\Status;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
        if (! $status || ! in_array($status, $allowedStatuses)) {
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
            'filters' => $filters,
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
            ->whereHas('status', fn ($q) => $q->where('name', $filters['status']))
            ->whereHas('userType', fn ($q) => $q->where('name', $filters['type']));

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

    // public function updateStatus(User $user, Request $request)
    // {
    //     $action = $request->input('action');

    //     if ($action === 'approve' && $user->status_id === Status::FOR_APPROVAL) {
    //         $user->update([
    //             'status_id' => Status::APPROVED,
    //         ]);
    //     }

    //     if ($action === 'decline' && $user->status_id === Status::FOR_APPROVAL) {
    //         $user->update([
    //             'status_id' => Status::ACTIVE,
    //         ]);
    //     }

    //     return back();
    // }

    public function updateStatus(User $user, Request $request)
    {
        $action = $request->input('action');

        if ($action === 'approve' && $user->status_id === Status::FOR_APPROVAL) {
            $user->update([
                'status_id' => Status::APPROVED,
            ]);

            // Notification 2: Membership prompt
            $user->notify(new GeneralNotification(
                type: 'coop_membership_available',
                title: 'You can now join the Cooperative!',
                body: 'You are now eligible to join the cooperative membership.',
                actionType: 'VIEW_MEMBERSHIP',
                route: '/(coop)/',
                extraData: [
                    'user_id' => $user->id,
                    'status' => 'eligible',
                ]
            ));

            // Notification 1: Profile approved
            $user->notify(new GeneralNotification(
                type: 'profile_approved',
                title: 'Profile Approved!',
                body: 'Your profile has been approved.',
                actionType: 'VIEW_PROFILE',
                route: '/profile',
                extraData: [
                    'user_id' => $user->id,
                    'status' => 'approved',
                ]
            ));
        }

        if ($action === 'decline' && $user->status_id === Status::FOR_APPROVAL) {
            $user->update([
                'status_id' => Status::REJECTED,
            ]);

            // Notification: Profile declined
            $user->notify(new GeneralNotification(
                type: 'profile_declined',
                title: 'Profile Update Declined',
                body: 'Your profile update was not approved. Please chat with our support team for more details.    ',
                actionType: 'VIEW_PROFILE',
                route: '/profile',
                extraData: [
                    'user_id' => $user->id,
                    'status' => 'declined',
                ]
            ));
        }

        return back();
    }
}
