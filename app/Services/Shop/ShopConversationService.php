<?php

namespace App\Services\Shop;

use App\Events\ShopMessageSent;
use App\Models\Shop;
use App\Models\ShopConversation;
use App\Models\ShopMessage;
use App\Models\User;
use App\Notifications\NewShopMessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ShopConversationService
{
    public function findOrCreateConversation(User $user, Shop $shop, ?Model $context = null): ShopConversation
    {
        $conversation = ShopConversation::firstOrCreate([
            'shop_id' => $shop->id,
            'user_id' => $user->id,
        ]);

        if ($context) {
            $conversation->pin($context);
        }

        return $conversation;
    }

    /**
     * @param  UploadedFile[]  $attachments
     */
    public function sendMessage(
        ShopConversation $conversation,
        User $sender,
        string $senderType,
        ?string $body,
        array $attachments = []
    ): ShopMessage {
        return DB::transaction(function () use ($conversation, $sender, $senderType, $body, $attachments) {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'sender_type' => $senderType,
                'body' => $body,
                'context_type' => $conversation->pinnable_type,
                'context_id' => $conversation->pinnable_id,
            ]);

            foreach ($attachments as $file) {
                $path = $file->store('shop-messages', 'public');

                $message->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $conversation->update(['last_message_at' => $message->created_at]);

            $this->notifyRecipient($conversation, $message);

            broadcast(new ShopMessageSent($message))->toOthers();

            return $message->load('attachments');
        });
    }

    public function markRead(ShopConversation $conversation, string $side): void
    {
        $column = $side === 'shop' ? 'shop_read_at' : 'user_read_at';

        $conversation->update([$column => now()]);
    }

    protected function notifyRecipient(ShopConversation $conversation, ShopMessage $message): void
    {
        if ($message->isFromShop()) {
            $conversation->user->notify(new NewShopMessageNotification($message));
        } else {
            $conversation->shop->user->notify(new NewShopMessageNotification($message));
        }
    }

    public function conversationsForUser(User $user, int $perPage = 20)
    {
        return ShopConversation::where('user_id', $user->id)
            ->with(['shop', 'latestMessage', 'pinnable'])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function conversationsForShop(Shop $shop, int $perPage = 20)
    {
        return ShopConversation::where('shop_id', $shop->id)
            ->with(['user', 'latestMessage', 'pinnable'])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }
}