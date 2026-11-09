<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'postal_code',
    'city',
    'province',
    'delivery_zone_id',
    'delivery_station_id',
    'cod_enabled',
    'is_active',
])]
class DeliveryServiceArea extends Model
{
    protected function casts(): array
    {
        return [
            'cod_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_station_id');
    }
}