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
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'messages' => function ($query) {
                    $query->latest();
                },
                'messages.sender',
                'participants.user',
                'conversable',
            ])
            ->get();

        $conversations->each(function ($conversation) use ($user) {
            $myParticipant = $conversation->participants->firstWhere('user_id', $user->id);
            $lastReadAt = $myParticipant?->last_read_at;
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

        $conversation->load(['messages.sender', 'messages.attachments', 'participants.user']);

        $conversation->messages->each(function ($message) {
            $message->attachments->each(function ($attachment) {
                $attachment->path = $attachment->path ? asset('storage/'.$attachment->path) : null;
            });
        });

        return $conversation;
    }

    public function markRead(Conversation $conversation, Request $request)
    {
        Gate::authorize('view', $conversation);

        $participant = $conversation->ensureParticipant($request->user());
        $participant->update(['last_read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
