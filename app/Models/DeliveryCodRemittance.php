<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'delivery_rider_id',
    'delivery_station_id',
    'expected_amount',
    'received_amount',
    'received_by',
    'remitted_at',
])]
class DeliveryCodRemittance extends Model
{
    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'remitted_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class, 'delivery_rider_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(DeliveryStation::class, 'delivery_station_id');
    }

    public function riderTasks(): HasMany
    {
        return $this->hasMany(DeliveryRiderTask::class, 'delivery_cod_remittance_id');
    }

    /** Rider hands cash to the station. Covers everything the rider has collected and not yet handed in. */
    public static function record(DeliveryRider $rider, string|float $receivedAmount, ?string $receivedBy = null): self
    {
        $tasks = $rider->unremittedCodTasks()->get();

        if ($tasks->isEmpty()) {
            throw new DomainException('This rider has no COD to hand in.');
        }

        return DB::transaction(function () use ($rider, $tasks, $receivedAmount, $receivedBy) {
            $remittance = self::create([
                'delivery_rider_id' => $rider->id,
                'delivery_station_id' => $rider->delivery_station_id,
                'expected_amount' => number_format($tasks->sum(fn ($task) => (float) $task->cod_collected), 2, '.', ''),
                'received_amount' => $receivedAmount,
                'received_by' => $receivedBy,
                'remitted_at' => now(),
            ]);

            foreach ($tasks as $task) {
                $task->update(['delivery_cod_remittance_id' => $remittance->id]);
                $task->shipment->update(['cod_remitted_at' => now()]);
            }

            return $remittance;
        });
    }

    /** Negative means the rider handed in less than collected. */
    public function variance(): float
    {
        return round((float) $this->received_amount - (float) $this->expected_amount, 2);
    }
}