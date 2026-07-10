<?php

use App\Models\Conversation;
use App\Models\ShopConversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// For Intellectual Property Conversations
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    return Gate::forUser($user)->allows('view', $conversation);
});

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

// For Shop Conversations
Broadcast::channel('shop-conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ShopConversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    if ($conversation->user_id === $user->id) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'user'];
    }

    if ($conversation->shop->user_id === $user->id) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'shop'];
    }

    return false;
});
