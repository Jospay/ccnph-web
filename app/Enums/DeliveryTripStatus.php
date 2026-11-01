<?php

namespace App\Enums;

enum DeliveryTripStatus: string
{
    case PLANNED = 'planned';
    case IN_TRANSIT = 'in_transit';
    case ARRIVED = 'arrived';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Planned',
            self::IN_TRANSIT => 'In Transit',
            self::ARRIVED => 'Arrived',
        };
    }
}