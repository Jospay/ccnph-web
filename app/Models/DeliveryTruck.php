<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'plate_number',
    'capacity_kg',
    'is_active',
])]
class DeliveryTruck extends Model
{
    protected function casts(): array
    {
        return [
            'capacity_kg' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(DeliveryTrip::class, 'delivery_truck_id');
    }
}