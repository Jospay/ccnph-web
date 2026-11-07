<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'code',
    'name',
])]
class DeliveryZone extends Model
{
    public function stations(): HasMany
    {
        return $this->hasMany(DeliveryStation::class, 'delivery_zone_id');
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(DeliveryServiceArea::class, 'delivery_zone_id');
    }

    public function originRates(): HasMany
    {
        return $this->hasMany(DeliveryRate::class, 'delivery_origin_zone_id');
    }

    public function destinationRates(): HasMany
    {
        return $this->hasMany(DeliveryRate::class, 'delivery_destination_zone_id');
    }
}