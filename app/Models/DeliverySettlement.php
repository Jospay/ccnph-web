<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\DeliverySettlementStatus;

#[Fillable([
    'delivery_client_id',
    'reference',
    'cut_off_at',
    'cod_total',
    'fee_total',
    'net_amount',
    'status',
    'paid_at',
])]
class DeliverySettlement extends Model
{
    protected function casts(): array
    {
        return [
            'status' => DeliverySettlementStatus::class,
            'cut_off_at' => 'datetime',
            'paid_at' => 'datetime',

            'cod_total' => 'decimal:2',
            'fee_total' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(DeliveryApiClient::class, 'delivery_client_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_settlement_id');
    }
}