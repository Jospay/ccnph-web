<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryServiceType;
use App\Enums\DeliveryPickupType;
use App\Enums\DeliveryShipmentStatus;

#[Fillable([
    'tracking_number',
    'delivery_client_id',
    'client_reference',
    'delivery_rate_id',
    'service_type',
    'pickup_type',
    'status',
    'is_returning',

    'sender_name',
    'sender_phone',
    'sender_address',
    'sender_postal_code',

    'recipient_name',
    'recipient_phone',
    'recipient_address',
    'recipient_postal_code',

    'item_description',

    'weight_grams',
    'length_cm',
    'width_cm',
    'height_cm',
    'chargeable_weight_grams',

    'declared_value',
    'cod_amount',
    'shipping_fee',
    'cod_fee',
    'insurance_fee',
    'return_fee',
    'total_fee',

    'delivery_origin_station_id',
    'delivery_destination_station_id',
    'delivery_current_station_id',
    'route',

    'delivery_attempts',
    'max_delivery_attempts',
    'eta_min_date',
    'eta_max_date',
    'cod_remitted_at',
    'completed_at',
    'delivery_settlement_id',
])]
class DeliveryShipment extends Model
{
    protected function casts(): array
    {
        return [
            'service_type' => DeliveryServiceType::class,
            'pickup_type' => DeliveryPickupType::class,
            'status' => DeliveryShipmentStatus::class,
            
            'is_returning' => 'boolean',
            'route' => 'array',

            'weight_grams' => 'integer',
            'length_cm' => 'integer',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
            'chargeable_weight_grams' => 'integer',

            'delivery_attempts' => 'integer',
            'max_delivery_attempts' => 'integer',

            'declared_value' => 'decimal:2',
            'cod_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'cod_fee' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'return_fee' => 'decimal:2',
            'total_fee' => 'decimal:2',

            'eta_min_date' => 'date',
            'eta_max_date' => 'date',
            'cod_remitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(DeliveryApiClient::class, 'delivery_client_id');
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(DeliveryRate::class, 'delivery_rate_id');
    }

    public function originStation(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_origin_station_id');
    }

    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_destination_station_id');
    }

    public function currentStation(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_current_station_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(DeliverySettlement::class, 'delivery_settlement_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeliveryShipmentEvent::class, 'delivery_shipment_id');
    }

    public function riderTasks(): HasMany
    {
        return $this->hasMany(DeliveryRiderTask::class, 'delivery_shipment_id');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(
            DeliveryTrip::class,
            'delivery_trip_shipments',
            'delivery_shipment_id',
            'delivery_trip_id',
        )->withTimestamps();
    }
}