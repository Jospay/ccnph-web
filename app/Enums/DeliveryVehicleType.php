<?php

namespace App\Enums;

enum DeliveryVehicleType: string
{
    case MOTORCYCLE = 'motorcycle';
    case BICYCLE = 'bicycle';
    case TRICYCLE = 'tricycle';
    case CAR = 'car';
    case VAN = 'van';

    public function label(): string
    {
        return match ($this) {
            self::MOTORCYCLE => 'Motorcycle',
            self::BICYCLE => 'Bicycle',
            self::TRICYCLE => 'Tricycle',
            self::CAR => 'Car',
            self::VAN => 'Van',
        };
    }
}