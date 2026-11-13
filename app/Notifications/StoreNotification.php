<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StoreNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $type  Unique string for filtering/icons (e.g. 'order_placed', 'stock_low')
     * @param  string  $title  The title displayed to the user
     * @param  string  $body  The main description message
     * @param  string  $actionType  Action key for the app UI (e.g. 'VIEW_ORDER', 'VIEW_STORE')
     * @param  string|null  $route  Direct Expo router path (e.g. '/(store)/orders/details?id=5')
     * @param  array  $extraData  Extra dynamic parameters (e.g. ['store_id' => 3, 'order_id' => 10])
     */
    public function __construct(
        public string $type,
        public string $title,
        public string $body,
        public string $actionType = 'NO_ACTION',
        public ?string $route = null,
        public array $extraData = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'action_type' => $this->actionType,
            'route' => $this->route,
        ], $this->extraData);
    }
}
