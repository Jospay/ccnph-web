<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string  $type  A unique string for filtering/icons (e.g. 'ip_approved', 'payment_received')
     * @param  string  $title  The title displayed to the user
     * @param  string  $body  The main description message
     * @param  string  $actionType  Action key for mobile/web UI (e.g. 'MAKE_PAYMENT', 'VIEW_PROPERTY')
     * @param  string|null  $route  Direct React Native/Expo router path (e.g. '(intellectual)/details?id=2')
     * @param  array  $extraData  Any extra dynamic parameters (e.g. ['property_id' => 3])
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
