<?php

namespace App\Services\Shop;

use App\Events\ShopMessageSent;
use App\Models\ShopConversation;
use App\Models\ShopMessage;
use App\Notifications\NewShopMessageNotification;

class ShopConversationNotifier
{
    /**
     * Notify the other participant in the conversation.
     */
    public function notifyOther(
        ShopConversation $conversation,
        ShopMessage $message,
        int $senderId
    ): void {
        $recipient = $senderId === $conversation->user_id
            ? $conversation->shop->user
            : $conversation->user;

        $recipient->notify(new NewShopMessageNotification($message));

        broadcast(new ShopMessageSent($message))->toOthers();
    }
}
