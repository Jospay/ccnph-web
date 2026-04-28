<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Http\Resources\PendingUserResource;
use App\Http\Resources\PendingUserDetailResource;
use Inertia\Response;
use Inertia\Inertia;

class CoopMembershipController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->where('user_type_id', UserType::BASIC)
            ->where('status_id', Status::PENDING_FOR_MEMBER)
            ->with([
                'status:id,name',
                'userType:id,name',
            ])
            ->get();

        return Inertia::render('coop-membership/Index', [
            'pendingUsers' => PendingUserResource::collection($users),
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'status:id,name',
            'userType:id,name',
        ]);

        return PendingUserDetailResource::make($user);
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
