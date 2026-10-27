<?php

namespace App\Enums;

enum DeliveryStationType: string
{
    case HUB = 'hub';
    case STATION = 'station';

    public function label(): string
    {
        return match ($this) {
            self::HUB => 'Hub',
            self::STATION => 'Station',
        };
    }
}