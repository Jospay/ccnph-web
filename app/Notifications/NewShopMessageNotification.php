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

    public $afterCommit = true;

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
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Summary of toMail
     * 
     * @param object $notifiable
     * @return MailMessage
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
            ->line(str($this->message->body)->limit(150))
            ->action('View Conversation', url("/conversations/{$conversation->id}"));
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'shop_conversation_id' => $conversation->id,
            'shop_message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
            'body' => str($this->message->body)->limit(100)->toString(),
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
