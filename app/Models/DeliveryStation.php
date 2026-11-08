<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryStationType;

#[Fillable([
    'code',
    'name',
    'type',
    'delivery_hub_id',
    'delivery_zone_id',
    'address',
    'city',
    'province',
    'phone',
    'latitude',
    'longitude',
    'is_active',
])]
class DeliveryStation extends Model
{
    protected function casts(): array
    {
        return [
            'type' => DeliveryStationType::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(self::class, 'delivery_hub_id');
    }

    public function childStations(): HasMany
    {
        return $this->hasMany(self::class, 'delivery_hub_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(DeliveryServiceArea::class, 'delivery_station_id');
    }

    public function riders(): HasMany
    {
        return $this->hasMany(DeliveryRider::class, 'delivery_station_id');
    }

    public function originTrips(): HasMany
    {
        return $this->hasMany(DeliveryTrip::class, 'delivery_origin_station_id');
    }

    public function destinationTrips(): HasMany
    {
        return $this->hasMany(DeliveryTrip::class, 'delivery_destination_station_id');
    }

    public function originShipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_origin_station_id');
    }

    public function destinationShipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_destination_station_id');
    }

    public function currentShipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_current_station_id');
    }

    public function shipmentEvents(): HasMany
    {
        return $this->hasMany(DeliveryShipmentEvent::class, 'delivery_station_id');
    }

    public function codRemittances(): HasMany
    {
        return $this->hasMany(DeliveryCodRemittance::class, 'delivery_station_id');
    }
}