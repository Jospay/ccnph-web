<?php

namespace App\Services\Conversation;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;

class ConversationNotifier
{
    /**
     * Create a new class instance.
     */
    public function notifyOthers(Conversation $conversation, Message $message, int $senderId): void
    {
        $participants = $conversation->participants()
            ->where('user_id', '!=', $senderId)
            ->with('user')
            ->get()
            ->pluck('user');

        $service = $conversation->conversable->service ?? null;
        $admins = $service ? $service->admins()->get() : collect();

        $participants->merge($admins)
            ->unique('id')
            ->reject(fn($user) => $user->id === $senderId)
            ->each(fn($user) => $user->notify(new NewMessageNotification($message)));

        broadcast(new MessageSent($message))->toOthers();
    }
}
