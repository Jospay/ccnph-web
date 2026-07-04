<?php

namespace App\Http\Controllers\API\Conversation;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function show(Conversation $conversation, Request $request)
    {
        Gate::authorize("view", $conversation);

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
