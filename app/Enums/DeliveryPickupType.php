<?php

namespace App\Enums;

enum DeliveryPickupType: string
{
    case PICKUP = 'pickup';
    case DROPOFF = 'dropoff';

    public function label(): string
    {
        return match ($this) {
            self::PICKUP => 'Pickup',
            self::DROPOFF => 'Drop-off',
        };
    }
}