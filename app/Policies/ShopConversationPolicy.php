<?php

namespace App\Policies;

use App\Models\ShopConversation;
use App\Models\User;

class ShopConversationPolicy
{
    public function view(User $user, ShopConversation $conversation): bool
    {
        return $conversation->user_id === $user->id
            || $conversation->shop->user_id === $user->id;
    }

    public function sendMessage(User $user, ShopConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    public function create(User $user): bool
    {
        return true;
    }
}