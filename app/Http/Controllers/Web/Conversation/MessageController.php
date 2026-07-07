<?php

namespace App\Http\Controllers\Web\Conversation;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Conversation\ConversationNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(private readonly ConversationNotifier $notifier)
    {
    }

    public function store(Conversation $conversation, Request $request): RedirectResponse
    {
        Gate::authorize('reply', $conversation);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachments'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store("conversations/{$conversation->id}/attachments", 'public');

            $message->attachments()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $participant = $conversation->ensureParticipant($request->user());
        $participant->update(['last_read_at' => now()]);

        $this->notifier->notifyOthers($conversation, $message, $request->user()->id);

        return back();
    }
}
