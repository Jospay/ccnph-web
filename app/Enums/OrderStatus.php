<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURN_REQUESTED = 'return_requested';
    case RETURN_APPROVED = 'return_approved';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Checkout',
            self::CONFIRMED => 'Order Confirmed',
            self::PROCESSING => 'Processing',
            self::PACKED => 'Ready to Ship',
            self::SHIPPED => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::RETURN_REQUESTED => 'Return Requested',
            self::RETURN_APPROVED => 'Return Approved',
            self::RETURNED => 'Returned / Refunded',
        };
    }
}