<?php

namespace App\Http\Controllers\API\Conversation;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Fetch all threads where this specific user is marked as an active participant
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'messages' => function ($query) {
                    $query->latest(); // Pull messages to look up text previews
                },
                'messages.sender',
                'participants.user',
                'conversable', // Pulls the underlying Intellectual Property record context
            ])
            ->get();

        // 2. Loop through and calculate the unread count for each individual thread
        $conversations->each(function ($conversation) use ($user) {
            // Find the current user's participant pivot record
            $myParticipant = $conversation->participants->firstWhere('user_id', $user->id);
            $lastReadAt = $myParticipant?->last_read_at;

            // Count messages sent by anyone else *after* the user's last read timestamp
            $conversation->unread_count = $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->when($lastReadAt, function ($query) use ($lastReadAt) {
                    $query->where('created_at', '>', $lastReadAt);
                })
                ->count();
        });

        return response()->json($conversations);
    }

    public function show(Conversation $conversation, Request $request)
    {
        Gate::authorize('view', $conversation);

        $conversation->ensureParticipant($request->user());

        return $conversation->load(['messages.sender', 'participants.user']);
    }

    public function markRead(Conversation $conversation, Request $request)
    {
        Gate::authorize('view', $conversation);

        $participant = $conversation->ensureParticipant($request->user());
        $participant->update(['last_read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
