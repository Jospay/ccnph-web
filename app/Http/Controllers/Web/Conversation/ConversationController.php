<?php

namespace App\Http\Controllers\Web\Conversation;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function show(Conversation $conversation, Request $request): Response
    {
        Gate::authorize('view', $conversation);

        $conversation->ensureParticipant($request->user());

        $conversation->load([
            'messages.sender',
            'messages.attachments',
            'participants.user',
            'conversable',
        ]);

        return Inertia::render('conversations/Show', [
            'conversation' => $conversation,
        ]);
    }

    public function markRead(Conversation $conversation, Request $request): RedirectResponse
    {
        Gate::authorize('view', $conversation);

        $participant = $conversation->ensureParticipant($request->user());
        $participant->update(['last_read_at' => now()]);

        return back();
    }
}
