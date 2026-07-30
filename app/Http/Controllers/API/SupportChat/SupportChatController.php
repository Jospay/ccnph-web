<?php

namespace App\Http\Controllers\API\SupportChat;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Services\SupportChat\SupportChatService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(
        private readonly SupportChatService $supportChatService
    ) {}

    /**
     * Check if the user already has a support conversation, returning
     * the first page of messages and unread count if so.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $support = $this->supportChatService->findForUser($user);

        if (! $support || ! $support->conversation) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            ...$this->buildConversationPayload($support->conversation, $user),
        ]);
    }

    /**
     * Start a new support conversation (or return the existing one).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $existing = $this->supportChatService->findForUser($user);

        if ($existing && $existing->conversation) {
            return response()->json([
                'exists' => true,
                ...$this->buildConversationPayload($existing->conversation, $user),
            ]);
        }

        $support = $this->supportChatService->createForUser($user);

        return response()->json([
            'exists' => true,
            'conversation' => $support->conversation->load('participants.user'),
            'messages' => [],
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'has_more' => false,
            ],
            'unread_count' => 0,
        ], 201);
    }

    private function buildConversationPayload($conversation, $user): array
    {
        // 1. Get user participant record to check last_read_at
        $myParticipant = $conversation->participants()
            ->where('user_id', $user->id)
            ->first();

        $lastReadAt = $myParticipant?->last_read_at;

        // 2. Fetch paginated messages
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->latest()
            ->paginate(15);

        $messages->setCollection($messages->getCollection()->reverse()->values());

        $messages->getCollection()->each(function ($message) {
            $message->attachments->each(function ($attachment) {
                $attachment->path = $attachment->path ? asset('storage/'.$attachment->path) : null;
            });
        });

        // 3. Calculate actual unread count for messages sent by OTHERS after last_read_at
        $unreadCount = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->when($lastReadAt, function ($query) use ($lastReadAt) {
                $query->where('created_at', '>', $lastReadAt);
            })
            ->count();

        return [
            'conversation' => $conversation->load('participants.user'),
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'has_more' => $messages->hasMorePages(),
            ],
            'unread_count' => $unreadCount,
        ];
    }

    public function markRead(Request $request)
    {
        $user = $request->user();

        // Find the user's support conversation
        $supportChat = SupportConversation::where('user_id', $user->id)->first();

        if ($supportChat && $supportChat->conversation) {
            $participant = $supportChat->conversation->ensureParticipant($user);
            $participant->update(['last_read_at' => now()]);
        }

        return response()->json(['status' => 'ok']);
    }
}
