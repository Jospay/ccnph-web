<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ShopConversation;

class OrderObserver
{
    protected array $terminalStatuses = [
        OrderStatus::DELIVERED->value,
        OrderStatus::CANCELLED->value,
        OrderStatus::RETURNED->value,
    ];

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        if (! in_array($order->status, $this->terminalStatuses)) {
            return;
        }

        ShopConversation::where('pinnable_type', Order::class)
            ->where('pinnable_id', $order->id)
            ->each(fn (ShopConversation $c) => $c->unpin());
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
