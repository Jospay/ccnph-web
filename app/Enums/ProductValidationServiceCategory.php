<?php

namespace App\Enums;

enum ProductValidationServiceCategory: string
{
    case FOOD = 'food';
    case RETAIL = 'retail';
    case AGRICULTURE = 'agriculture';
    case DIGITAL = 'digital';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FOOD => 'Food',
            self::RETAIL => 'Retail',
            self::AGRICULTURE => 'Agriculture',
            self::DIGITAL => 'Digital',
            self::OTHER => 'Other',
        };
    }
}
