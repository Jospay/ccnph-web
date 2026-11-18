<?php

namespace App\Enums;

enum CooperativeNationalStatus: string
{
    case FOR_APPROVAL = 'for_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::FOR_APPROVAL => 'For Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
