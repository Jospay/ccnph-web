<?php

namespace App\Services\SupportChat;

use App\Models\Conversation;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupportChatService
{
    /**
     * Find the existing support conversation for a user, if any.
     */
    public function findForUser(User $user): ?SupportConversation
    {
        return SupportConversation::where('user_id', $user->id)
            ->with('conversation')
            ->latest()
            ->first();
    }

    /**
     * Create a brand new support conversation for the user.
     */
    public function createForUser(User $user): SupportConversation
    {
        return DB::transaction(function () use ($user) {

            $support = SupportConversation::create([
                'user_id' => $user->id,
                'status' => 'open',
            ]);

            $conversation = new Conversation(['status' => 'open']);
            $support->conversation()->save($conversation);

            $conversation->participants()->create([
                'user_id' => $user->id,
                'role' => 'user',
            ]);

            return $support->load('conversation');
        });
    }
}
