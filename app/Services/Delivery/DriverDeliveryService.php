<?php

namespace App\Services\Delivery;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Driver;
use App\Models\OrderDelivery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * What the driver app can do. Every step also moves the customer-facing order,
 * so the customer's "Track order" screen (shipped_at / delivered_at) updates by itself.
 */
class DriverDeliveryService
{
    /** Driver collected the parcel from the shop -> order becomes SHIPPED. */
    public function pickUp(OrderDelivery $delivery, Driver $driver): OrderDelivery
    {
        return DB::transaction(function () use ($delivery, $driver) {
            $delivery = $this->lock($delivery, $driver);

            if ($delivery->status !== DeliveryStatus::ASSIGNED) {
                throw ValidationException::withMessages(['delivery' => 'This order is not waiting for pickup.']);
            }

            $order = $delivery->order;

            if ($order->status !== OrderStatus::PACKED) {
                throw ValidationException::withMessages(['delivery' => 'The order is no longer ready for pickup.']);
            }

            $delivery->update([
                'status' => DeliveryStatus::PICKED_UP,
                'picked_up_at' => now(),
                'attempts' => $delivery->attempts + 1,
            ]);

            $order->update([
                'status' => OrderStatus::SHIPPED,
                'shipped_at' => now(),
            ]);

            return $delivery;
        });
    }

    /** Parcel handed to the customer -> order becomes DELIVERED (customer then confirms / completes it). */
    public function deliver(OrderDelivery $delivery, Driver $driver, ?UploadedFile $proof = null, ?string $remarks = null): OrderDelivery
    {
        return DB::transaction(function () use ($delivery, $driver, $proof, $remarks) {
            $delivery = $this->lock($delivery, $driver);

            if ($delivery->status !== DeliveryStatus::PICKED_UP) {
                throw ValidationException::withMessages(['delivery' => 'Pick up the order before marking it delivered.']);
            }

            $order = $delivery->order;

            if ($order->status !== OrderStatus::SHIPPED) {
                throw ValidationException::withMessages(['delivery' => 'The order is not out for delivery.']);
            }

            $proofPath = $proof?->store('delivery_proofs', 'public');

            $delivery->update([
                'status' => DeliveryStatus::DELIVERED,
                'delivered_at' => now(),
                'proof_photo' => $proofPath,
                'remarks' => $remarks,
                // COD: the driver collects the order total in cash
                'cod_amount_collected' => $order->isCod() ? $order->total : null,
            ]);

            $order->update([
                'status' => OrderStatus::DELIVERED,
                'delivered_at' => now(),
            ]);

            return $delivery;
        });
    }

    /**
     * Customer not available / wrong address / refused.
     * The order goes back to PACKED so the admin can dispatch it again.
     */
    public function fail(OrderDelivery $delivery, Driver $driver, string $reason): OrderDelivery
    {
        return DB::transaction(function () use ($delivery, $driver, $reason) {
            $delivery = $this->lock($delivery, $driver);

            if ($delivery->status !== DeliveryStatus::PICKED_UP) {
                throw ValidationException::withMessages(['delivery' => 'Only orders that are out for delivery can fail.']);
            }

            $delivery->update([
                'status' => DeliveryStatus::FAILED,
                'failed_at' => now(),
                'failure_reason' => $reason,
            ]);

            $delivery->order->update([
                'status' => OrderStatus::PACKED,
                'shipped_at' => null,
            ]);

            return $delivery;
        });
    }

    private function lock(OrderDelivery $delivery, Driver $driver): OrderDelivery
    {
        $locked = OrderDelivery::whereKey($delivery->id)
            ->where('driver_id', $driver->id) // a driver can only touch his own deliveries
            ->lockForUpdate()
            ->with('order.checkout.payment')
            ->first();

        abort_if(! $locked, 404, 'Delivery not found.');

        return $locked;
    }
}
