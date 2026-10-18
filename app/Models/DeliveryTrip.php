<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryTripStatus;

#[Fillable([
    'code',
    'truck_id',
    'delivery_origin_station_id',
    'delivery_destination_station_id',
    'driver_name',
    'driver_phone',
    'status',
    'scheduled_at',
    'departed_at',
    'arrived_at',
])]
class DeliveryTrip extends Model
{
    protected function casts(): array
    {
        return [
            'status' => DeliveryTripStatus::class,
            'scheduled_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
        ];
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(DeliveryTruck::class, 'delivery_truck_id');
    }

    public function originStation(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_origin_station_id');
    }

    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_destination_station_id');
    }

    public function shipments(): BelongsToMany
    {
        return $this->belongsToMany(
            DeliveryShipment::class,
            'delivery_trip_shipments',
            'delivery_trip_id',
            'delivery_shipment_id',
        )->withTimestamps();
    }

    public function shipmentEvents(): HasMany
    {
        return $this->hasMany(DeliveryShipmentEvent::class, 'delivery_trip_id');
    }
}