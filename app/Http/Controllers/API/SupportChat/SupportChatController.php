<?php

namespace App\Http\Controllers\API\SupportChat;

use App\Http\Controllers\Controller;
use App\Services\SupportChat\SupportChatService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(
        private readonly SupportChatService $supportChatService
    ) {}

    /**
     * Check if the user already has a support conversation, returning
     * the first page of messages if so.
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
            ...$this->buildConversationPayload($support->conversation),
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
                ...$this->buildConversationPayload($existing->conversation),
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
        ], 201);
    }

    private function buildConversationPayload($conversation): array
    {
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

        return [
            'conversation' => $conversation->load('participants.user'),
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'has_more' => $messages->hasMorePages(),
            ],
        ];
    }
}
