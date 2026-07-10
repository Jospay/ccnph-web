<?php

namespace App\Policies;

use App\Models\ShopConversation;
use App\Models\User;

class ShopConversationPolicy
{
    public function view(User $user, ShopConversation $conversation): bool
    {
        return $user->id === $conversation->buyer_id || $user->id === $conversation->seller_id;
    }

    public function reply(User $user, ShopConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
