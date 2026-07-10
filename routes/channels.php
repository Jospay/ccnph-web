<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

Broadcast::channel('conversation.{conversationId}', function (User $user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
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
