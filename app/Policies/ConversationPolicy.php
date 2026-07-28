<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\SupportConversation;
use App\Models\User;
use App\Models\UserType;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($conversation->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($conversation->conversable instanceof SupportConversation) {
            return $user->user_type === UserType::ADMIN;
        }

        $serviceId = $conversation->conversable->service_id ?? null;

        return $serviceId && $user->managesService($serviceId);
    }

    public function reply(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
