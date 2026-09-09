<?php

namespace App\Enums;

enum ProductValidationServiceCategory: string
{
    case SAAS = 'saas';
    case ECOMMERCE = 'ecommerce';
    case MOBILE_APP = 'mobile_app';
    case PHYSICAL_PRODUCT = 'physical_product';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SAAS => 'SaaS',
            self::ECOMMERCE => 'Ecommerce',
            self::MOBILE_APP => 'Mobile App',
            self::PHYSICAL_PRODUCT => 'Physical Product',
            self::OTHER => 'Other',
        };
    }
}
