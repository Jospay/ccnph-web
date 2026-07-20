<?php

namespace App\Notifications;

use App\Models\ShopMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewShopMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ShopMessage $message)
    {
        // Queue notification only after DB transaction commits.
        $this->afterCommit = true;
    }

    /**
     * Delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Mail notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;
        $senderName = $this->message->isFromShop()
            ? $conversation->shop->name
            : $conversation->user->name;

        return (new MailMessage)
            ->subject("New message from {$senderName}")
            ->line("You have a new message from {$senderName}.")
            ->line(Str::limit($this->message->body ?? '', 150))
            ->action(
                'View Conversation',
                url("/conversations/{$conversation->id}")
            );
    }

    /**
     * Database notification payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            // Conversation
            'conversation_id' => $conversation->id,
            'shop_conversation_id' => $conversation->id,

            // Shop
            'shop_id' => $conversation->shop->id,
            'shop_name' => $conversation->shop->name,

            // Message
            'message_id' => $this->message->id,
            'shop_message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'body' => Str::limit($this->message->body ?? '', 100),
        ];
    }

    /**
     * Broadcast notification payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $conversation = $this->message->conversation;

        return new BroadcastMessage([
            // Conversation
            'conversation_id' => $conversation->id,
            'shop_conversation_id' => $conversation->id,

            // Shop
            'shop_id' => $conversation->shop->id,
            'shop_name' => $conversation->shop->name,

            // Message
            'message_id' => $this->message->id,
            'shop_message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'body' => Str::limit($this->message->body ?? '', 100),
        ]);
    }
}
