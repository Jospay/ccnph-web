<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliveryServiceType;

#[Fillable([
    'delivery_origin_zone_id',
    'delivery_destination_zone_id',
    'service_type',
    'base_fee',
    'base_weight_grams',
    'step_weight_grams',
    'step_fee',
    'max_weight_grams',
    'volumetric_divisor',
    'cod_fee_percent',
    'cod_min_fee',
    'insurance_percent',
    'return_fee',
    'eta_min_days',
    'eta_max_days',
    'is_active',
])]
class DeliveryRate extends Model
{
    protected function casts(): array
    {
        return [
            'service_type' => DeliveryServiceType::class,
            
            'base_fee' => 'decimal:2',
            'step_fee' => 'decimal:2',
            'cod_fee_percent' => 'decimal:2',
            'cod_min_fee' => 'decimal:2',
            'insurance_percent' => 'decimal:2',
            'return_fee' => 'decimal:2',

            'base_weight_grams' => 'integer',
            'step_weight_grams' => 'integer',
            'max_weight_grams' => 'integer',
            'volumetric_divisor' => 'integer',
            'eta_min_days' => 'integer',
            'eta_max_days' => 'integer',

            'is_active' => 'boolean',
        ];
    }

    public function originZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_origin_zone_id');
    }

    public function destinationZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_destination_zone_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_rate_id');
    }

    public static function findFor(int $originZoneId, int $destinationZoneId, string $serviceType): self
    {
        $rate = self::where('delivery_origin_zone_id', $originZoneId)
            ->where('delivery_destination_zone_id', $destinationZoneId)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();

        if (! $rate) {
            throw new DomainException("No {$serviceType} rate for this route.");
        }

        return $rate;
    }
}