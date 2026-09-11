<?php

namespace App\Enums;

enum AskExpertRequestCategory: string
{
    case BUSINESS_PLANNING = 'business_planning';
    case MARKETING_SALES = 'marketing_sales';
    case LEGAL_PERMITS = 'legal_permits';
    case FINANCE_FUNDING = 'finance_funding';
    case TECHNOLOGY = 'technology';
    case AGRICULTURE = 'agriculture';

    public function label(): string
    {
        return match ($this) {
            self::BUSINESS_PLANNING => 'Business Planning',
            self::MARKETING_SALES => 'Marketing & Sales',
            self::LEGAL_PERMITS => 'Legal & Permits',
            self::FINANCE_FUNDING => 'Finance & Funding',
            self::TECHNOLOGY => 'Technology / IT',
            self::AGRICULTURE => 'Agriculture',
        };
    }
}
