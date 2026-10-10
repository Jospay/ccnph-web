<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryShipmentStatus;

#[Fillable([
    'delivery_shipment_id',
    'status',
    'delivery_station_id',
    'delivery_rider_id',
    'delivery_trip_id',
    'note',
    'created_at',
])]
class DeliveryShipmentEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status' => DeliveryShipmentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_station_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class, 'delivery_rider_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(DeliveryTrip::class, 'delivery_trip_id');
    }
}