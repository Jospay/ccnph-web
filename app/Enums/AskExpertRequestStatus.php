<?php

namespace App\Enums;

enum AskExpertRequestStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_USER = 'waiting_for_user';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING_FOR_USER => 'Waiting For User',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
