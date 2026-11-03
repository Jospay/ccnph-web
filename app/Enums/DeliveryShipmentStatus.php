<?php

namespace App\Enums;

enum DeliveryShipmentStatus: string
{
    case BOOKED = 'booked';
    case AWAITING_PICKUP = 'awaiting_pickup';
    case PICKED_UP = 'picked_up';
    case AT_ORIGIN_STATION = 'at_origin_station';
    case IN_TRANSIT = 'in_transit';
    case AT_DESTINATION_STATION = 'at_destination_station';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERY_FAILED = 'delivery_failed';
    case DELIVERED = 'delivered';
    case OUT_FOR_RETURN = 'out_for_return';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::BOOKED => 'Booked',
            self::AWAITING_PICKUP => 'Awaiting Pickup',
            self::PICKED_UP => 'Picked Up',
            self::AT_ORIGIN_STATION => 'At Origin Station',
            self::IN_TRANSIT => 'In Transit',
            self::AT_DESTINATION_STATION => 'At Destination Station',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERY_FAILED => 'Delivery Failed',
            self::DELIVERED => 'Delivered',
            self::OUT_FOR_RETURN => 'Out for Return',
            self::RETURNED => 'Returned',
            self::CANCELLED => 'Cancelled',
            self::LOST => 'Lost',
        };
    }
}