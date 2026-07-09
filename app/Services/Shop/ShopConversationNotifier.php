<?php

namespace App\Services\Shop;

use App\Events\ShopMessageSent;
use App\Models\ShopConversation;
use App\Models\ShopMessage;
use App\Notifications\NewShopMessageNotification;

class ShopConversationNotifier
{
    /**
     * Create a new class instance.
     */
    public function notifyOther(ShopConversation $conversation, ShopMessage $message, int $senderId): void
    {
        $recipient = $senderId === $conversation->buyer_id
            ? $conversation->seller
            : $conversation->buyer;

        $recipient->notify(new NewShopMessageNotification($message));

        broadcast(new ShopMessageSent($message))->toOthers();
    }
}
