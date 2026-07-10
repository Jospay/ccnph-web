<?php

namespace App\Notifications;

use App\Models\ShopMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
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
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'shop_conversation_id' => $this->message->shop_conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'body' => Str::limit($this->message->body ?? '', 100),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'shop_conversation_id' => $this->message->shop_conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'body' => Str::limit($this->message->body ?? '', 100),
        ]);
    }
}
