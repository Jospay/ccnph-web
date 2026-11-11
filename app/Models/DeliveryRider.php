<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryVehicleType;
use App\Enums\DeliveryRiderTaskStatus;

#[Fillable([
    'delivery_station_id',
    'name',
    'phone',
    'vehicle_type',
    'plate_number',
    'api_token_hash',
    'is_active',
])]
class DeliveryRider extends Model
{
    protected $hidden = [
        'api_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => DeliveryVehicleType::class,
            'is_active' => 'boolean',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_station_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(DeliveryRiderTask::class, 'delivery_rider_id');
    }

    public function shipmentEvents(): HasMany
    {
        return $this->hasMany(DeliveryShipmentEvent::class, 'delivery_rider_id');
    }

    public function codRemittances(): HasMany
    {
        return $this->hasMany(DeliveryCodRemittance::class, 'delivery_rider_id');
    }

    /** Today's run sheet: everything assigned and not finished. */
    public function openTasks(): HasMany
    {
        return $this->tasks()->where('status', DeliveryRiderTaskStatus::ASSIGNED->value);
    }

    public function unremittedCodTasks(): HasMany
    {
        return $this->tasks()
            ->where('status', DeliveryRiderTaskStatus::COMPLETED->value)
            ->where('cod_collected', '>', 0)
            ->whereNull('delivery_cod_remittance_id');
    }
}