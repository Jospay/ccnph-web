<?php

namespace App\Enums;

enum ProductValidationServiceGoal: string
{
    case PRODUCT_MARKET_FIT = 'product_market_fit';
    case MARKET_DEMAND = 'market_demand';
    case CUSTOMER_INTEREST = 'customer_interest';
    case PRICING_VALIDATION = 'pricing_validation';
    case COMPETITIVE_ANALYSIS = 'competitive_analysis';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT_MARKET_FIT => 'Product Market Fit',
            self::MARKET_DEMAND => 'Market Demand',
            self::CUSTOMER_INTEREST => 'Customer Interest',
            self::PRICING_VALIDATION => 'Pricing Validation',
            self::COMPETITIVE_ANALYSIS => 'Competitive Analysis',
            self::OTHER => 'Other',
        };
    }
}
