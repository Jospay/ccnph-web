<?php

namespace App\Enums;

enum DeliveryServiceType: string
{
    case STANDARD = 'standard';
    case EXPRESS = 'express';
    case SAME_DAY = 'same_day';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::EXPRESS => 'Express',
            self::SAME_DAY => 'Same Day',
        };
    }
}