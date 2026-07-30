<?php

namespace App\Http\Controllers\Web\SupportChat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportChatController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tickets = SupportConversation::with([
            'user:id,name,email',
            'conversation.messages' => function ($query) {
                $query->latest()->limit(1);
            },
            'conversation.messages.sender:id,name',
            'conversation.participants',
        ])
            ->whereHas('conversation')
            ->get();

        $formattedTickets = $tickets
            ->map(function (SupportConversation $ticket) use ($user) {
                $conversation = $ticket->conversation;
                $latestMessage = $conversation?->messages->first();

                $myParticipant = $conversation?->participants
                    ->firstWhere('user_id', $user->id);

                $lastReadAt = $myParticipant?->last_read_at;

                $unreadCount = $conversation
                    ? $conversation->messages()
                        ->where('sender_id', '!=', $user->id)
                        ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
                        ->count()
                    : 0;

                return [
                    'id' => $ticket->id,
                    'conversation_id' => $conversation?->id,
                    'status' => $ticket->status,
                    'subject' => $ticket->subject,
                    'user' => $ticket->user,
                    'last_message' => $latestMessage?->body ?? ($latestMessage ? 'Sent an attachment' : null),
                    'last_message_at' => $latestMessage?->created_at,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc(fn ($ticket) => $ticket['last_message_at'] ?? $ticket['id'])
            ->values();

        // Selected conversation logic
        $selectedConversation = null;
        $activeConversationId = $request->query('conversation_id')
            ?? $formattedTickets->first()['conversation_id']
            ?? null;

        if ($activeConversationId) {
            $conversation = Conversation::with([
                'messages.sender',
                'messages.attachments',
                'participants.user',
                'conversable',
            ])->find($activeConversationId);

            if ($conversation) {
                // Ensure the current support/admin user is attached as a participant
                $conversation->ensureParticipant($user);
                $selectedConversation = $conversation;
            }
        }

        return Inertia::render('support-chat/Index', [
            'tickets' => $formattedTickets,
            'selectedConversation' => $selectedConversation,
        ]);
    }
}
