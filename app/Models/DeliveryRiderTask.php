<?php

namespace App\Models;

use App\Enums\DeliveryRiderTaskStatus;
use App\Enums\DeliveryRiderTaskType;
use App\Enums\DeliveryShipmentStatus;
use App\Enums\DeliveryPickupType;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'delivery_rider_id',
    'delivery_shipment_id',
    'type',
    'status',
    'attempt_no',
    'failure_reason',
    'received_by',
    'pod_photo_url',
    'cod_collected',
    'delivery_cod_remittance_id',
    'finished_at',
])]
class DeliveryRiderTask extends Model
{
    protected function casts(): array
    {
        return [
            'type' => DeliveryRiderTaskType::class,
            'status' => DeliveryRiderTaskStatus::class,
            'attempt_no' => 'integer',
            'cod_collected' => 'decimal:2',
            'finished_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class, 'delivery_rider_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }

    public function codRemittance(): BelongsTo
    {
        return $this->belongsTo(DeliveryCodRemittance::class, 'delivery_cod_remittance_id');
    }

    /**
     * Assign a shipment to a rider.
     * The task type is determined by the shipment's current delivery stage.
     */
    public static function assign(DeliveryShipment $shipment, DeliveryRider $rider): self
    {
        return DB::transaction(function () use ($shipment, $rider) {
            // Reload and lock the shipment to prevent simultaneous assignments.
            $shipment = DeliveryShipment::query()
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            $rider = DeliveryRider::query()
                ->lockForUpdate()
                ->findOrFail($rider->id);

            if (! $rider->is_active) {
                throw new DomainException('This rider is inactive.');
            }

            [$type, $stationId, $nextStatus] = match (true) {

                // Initial pickup from the sender.
                $shipment->status === DeliveryShipmentStatus::BOOKED
                    && $shipment->pickup_type === DeliveryPickupType::PICKUP
                    => [
                        DeliveryRiderTaskType::PICKUP,
                        $shipment->delivery_origin_station_id,
                        null,
                    ],

                // Return delivery to the original sender.
                $shipment->status === DeliveryShipmentStatus::AT_ORIGIN_STATION
                    && $shipment->is_returning
                    && $shipment->delivery_current_station_id === $shipment->delivery_origin_station_id
                    => [
                        DeliveryRiderTaskType::RETURN,
                        $shipment->delivery_origin_station_id,
                        DeliveryShipmentStatus::OUT_FOR_RETURN,
                    ],

                // Final delivery to the recipient.
                $shipment->status === DeliveryShipmentStatus::AT_DESTINATION_STATION
                    && ! $shipment->is_returning
                    && $shipment->delivery_current_station_id === $shipment->delivery_destination_station_id
                    => [
                        DeliveryRiderTaskType::DELIVERY,
                        $shipment->delivery_destination_station_id,
                        DeliveryShipmentStatus::OUT_FOR_DELIVERY,
                    ],

                default => throw new DomainException(
                    "Shipment {$shipment->tracking_number} is not ready for a rider."
                ),
            };

            if ((int) $rider->delivery_station_id !== (int) $stationId) {
                throw new DomainException('This rider belongs to another station.');
            }

            // A shipment can only have one currently assigned rider task.
            if (
                $shipment->riderTasks()
                    ->where('status', DeliveryRiderTaskStatus::ASSIGNED->value)
                    ->exists()
            ) {
                throw new DomainException(
                    "Shipment {$shipment->tracking_number} already has an assigned rider."
                );
            }

            $task = self::create([
                'delivery_rider_id' => $rider->id,
                'delivery_shipment_id' => $shipment->id,
                'type' => $type,
                'status' => DeliveryRiderTaskStatus::ASSIGNED,
                'attempt_no' => $type === DeliveryRiderTaskType::DELIVERY
                    ? $shipment->delivery_attempts + 1
                    : 1,
                'cod_collected' => 0,
            ]);

            // Pickup assignment does not change the shipment status.
            if ($nextStatus !== null) {
                $shipment->status = $nextStatus;
                $shipment->save();

                self::recordShipmentEvent(
                    $shipment,
                    $task,
                    $nextStatus,
                );
            }

            return $task;
        });
    }

    /**
     * Complete the rider task.
     * COD must exactly match the shipment's COD amount for delivery tasks.
     */
    public function complete(
        ?string $receivedBy = null,
        ?string $podPhotoUrl = null,
        string|int|float $codCollected = 0,
    ): void {
        DB::transaction(function () use ($receivedBy, $podPhotoUrl, $codCollected) {

            // Always check the latest task status while holding a lock.
            $task = self::query()
                ->lockForUpdate()
                ->findOrFail($this->id);

            $task->ensureOpen();

            $shipment = DeliveryShipment::query()
                ->lockForUpdate()
                ->findOrFail($task->delivery_shipment_id);

            if (
                $task->type === DeliveryRiderTaskType::DELIVERY
                && self::amountToCents($codCollected)
                    !== self::amountToCents($shipment->cod_amount)
            ) {
                throw new DomainException(
                    "COD must be exactly {$shipment->cod_amount}."
                );
            }

            $task->update([
                'status' => DeliveryRiderTaskStatus::COMPLETED,
                'received_by' => $receivedBy,
                'pod_photo_url' => $podPhotoUrl,
                'cod_collected' => $task->type === DeliveryRiderTaskType::DELIVERY
                    ? $codCollected
                    : 0,
                'finished_at' => now(),
            ]);

            $nextStatus = match ($task->type) {
                DeliveryRiderTaskType::PICKUP => DeliveryShipmentStatus::PICKED_UP,
                DeliveryRiderTaskType::DELIVERY => DeliveryShipmentStatus::DELIVERED,
                DeliveryRiderTaskType::RETURN => DeliveryShipmentStatus::RETURNED,
            };

            $shipment->status = $nextStatus;
            $shipment->save();

            self::recordShipmentEvent(
                $shipment,
                $task,
                $nextStatus,
            );
        });

        // Keep the original model instance synchronized.
        $this->refresh();
    }

    /**
     * Mark the rider task as failed.
     * Failed delivery attempts are counted toward the return threshold.
     */
    public function fail(string $reason): void
    {
        DB::transaction(function () use ($reason) {

            $task = self::query()
                ->lockForUpdate()
                ->findOrFail($this->id);

            $task->ensureOpen();

            $shipment = DeliveryShipment::query()
                ->lockForUpdate()
                ->findOrFail($task->delivery_shipment_id);

            $task->update([
                'status' => DeliveryRiderTaskStatus::FAILED,
                'failure_reason' => $reason,
                'finished_at' => now(),
            ]);

            // Failed pickup:
            // Leave the shipment status unchanged so another rider can be assigned.
            if ($task->type === DeliveryRiderTaskType::PICKUP) {
                self::recordShipmentEvent(
                    $shipment,
                    $task,
                    $shipment->status,
                    $reason,
                );

                return;
            }

            // Failed recipient delivery:
            // Count the failed attempt and determine whether the parcel must be returned.
            if ($task->type === DeliveryRiderTaskType::DELIVERY) {
                $shipment->delivery_attempts += 1;

                $shipment->is_returning =
                    $shipment->delivery_attempts >= $shipment->max_delivery_attempts;
            }

            // Failed recipient delivery or failed return delivery.
            $shipment->status = DeliveryShipmentStatus::DELIVERY_FAILED;
            $shipment->save();

            self::recordShipmentEvent(
                $shipment,
                $task,
                DeliveryShipmentStatus::DELIVERY_FAILED,
                $reason,
            );
        });

        $this->refresh();
    }

    /**
     * Ensure the rider task has not already been completed, failed, or cancelled.
     */
    private function ensureOpen(): void
    {
        if ($this->status !== DeliveryRiderTaskStatus::ASSIGNED) {
            throw new DomainException(
                "This task is already {$this->status->value}."
            );
        }
    }

    /**
     * Record a shipment tracking event.
     */
    private static function recordShipmentEvent(
        DeliveryShipment $shipment,
        DeliveryRiderTask $task,
        DeliveryShipmentStatus $status,
        ?string $note = null,
    ): void {
        $stationId = match ($task->type) {
            DeliveryRiderTaskType::PICKUP => $shipment->delivery_origin_station_id,
            DeliveryRiderTaskType::DELIVERY => $shipment->delivery_destination_station_id,
            DeliveryRiderTaskType::RETURN => $shipment->delivery_origin_station_id,
        };

        $shipment->events()->create([
            'status' => $status,
            'delivery_station_id' => $stationId,
            'delivery_rider_id' => $task->delivery_rider_id,
            'note' => $note,
        ]);
    }

    /**
     * Convert a monetary amount to integer cents without floating-point comparison.
     */
    private static function amountToCents(string|int|float $amount): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/\A(-?)(\d+)(?:\.(\d{1,2}))?\z/', $value, $matches)) {
            throw new DomainException('COD amount must have at most two decimal places.');
        }

        $whole = (int) $matches[2];
        $fraction = (int) str_pad($matches[3] ?? '', 2, '0');

        $cents = ($whole * 100) + $fraction;

        return ($matches[1] ?? '') === '-' ? -$cents : $cents;
    }
}
