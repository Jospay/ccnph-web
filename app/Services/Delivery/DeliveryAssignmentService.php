<?php

namespace App\Services\Delivery;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Driver;
use App\Models\DriverArea;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Store\CheckoutService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentService
{
    /**
     * Orders the delivery admin can hand to a driver.
     * PACKED = the seller finished packing, the parcel is waiting for pickup.
     */
    public const ASSIGNABLE_STATUSES = [OrderStatus::PACKED];

    public const MAX_ORDERS_PER_ASSIGNMENT = 200;

    /**
     * Same visibility rule the customer side uses: COD, or paid, or legacy orders.
     * Unpaid online orders are never dispatched.
     */
    public function visibleOrders(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereDoesntHave('checkout.payment')
                ->orWhereHas('checkout.payment', function (Builder $payment) {
                    $payment->where('payment_method_id', PaymentMethod::CASH_ON_DELIVERY)
                        ->orWhere('status_id', CheckoutService::PAYMENT_STATUS_PAID);
                });
        });
    }

    /**
     * Base query for the "orders to dispatch" screen.
     * $scope: unassigned | assigned | all
     */
    public function ordersQuery(array $filters = [], string $scope = 'unassigned'): Builder
    {
        $query = $this->visibleOrders(Order::query());

        if ($scope === 'unassigned') {
            $query->whereIn('status', self::ASSIGNABLE_STATUSES)
                ->where(function (Builder $q) {
                    $q->whereDoesntHave('delivery')
                        // a failed attempt can be dispatched again
                        ->orWhereHas('delivery', fn (Builder $d) => $d->where('status', DeliveryStatus::FAILED));
                });
        } elseif ($scope === 'assigned') {
            $query->whereHas('delivery', fn (Builder $d) => $d->whereIn('status', [
                DeliveryStatus::ASSIGNED, DeliveryStatus::PICKED_UP,
            ]));
        }

        return $this->applyLocationFilters($query, $filters)
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $w) use ($search) {
                    $w->where('order_number', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            });
    }

    public function applyLocationFilters(Builder $query, array $filters): Builder
    {
        foreach (['region', 'province', 'city', 'barangay'] as $field) {
            if (! empty($filters[$field])) {
                $query->whereRaw("LOWER(TRIM({$field})) = ?", [DriverArea::normalize($filters[$field])]);
            }
        }

        return $query;
    }

    /**
     * Distribution by city (what the admin sees as chips / summary):
     * [{region, province, city, total}] for orders waiting for a driver.
     */
    public function cityDistribution(array $filters = []): Collection
    {
        $base = $this->ordersQuery(Arr::except($filters, ['city', 'barangay', 'search']), 'unassigned');

        return $base
            ->select('region', 'province', 'city', DB::raw('COUNT(*) as total'))
            ->groupBy('region', 'province', 'city')
            ->orderByDesc('total')
            ->orderBy('city')
            ->get();
    }

    /**
     * Drivers that cover a given address (city is required, barangay narrows it).
     * Whole-city drivers are always included.
     */
    public function eligibleDrivers(string $city, ?string $province = null, ?string $barangay = null): Collection
    {
        $city = DriverArea::normalize($city);
        $province = $province ? DriverArea::normalize($province) : null;
        $barangay = $barangay ? DriverArea::normalize($barangay) : null;

        return Driver::query()
            ->where('is_active', true)
            ->whereHas('areas', function (Builder $a) use ($city, $province, $barangay) {
                $a->whereRaw('LOWER(TRIM(city)) = ?', [$city])
                    ->when($province, fn (Builder $q) => $q->whereRaw('LOWER(TRIM(province)) = ?', [$province]))
                    ->when($barangay, function (Builder $q) use ($barangay) {
                        $q->where(function (Builder $b) use ($barangay) {
                            $b->whereNull('barangay')
                                ->orWhere('barangay', '')
                                ->orWhereRaw('LOWER(TRIM(barangay)) = ?', [$barangay]);
                        });
                    });
            })
            ->with('areas')
            ->withCount(['activeDeliveries as active_deliveries_count'])
            ->orderBy('active_deliveries_count') // least busy first
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Assign many orders to one driver.
     * Everything is checked before anything is written (all or nothing).
     *
     * @param  array<int>  $orderIds
     * @return Collection<int, OrderDelivery>
     */
    public function assign(array $orderIds, Driver $driver, User $admin): Collection
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));

        if (count($orderIds) === 0 || count($orderIds) > self::MAX_ORDERS_PER_ASSIGNMENT) {
            throw ValidationException::withMessages([
                'order_ids' => 'Select between 1 and '.self::MAX_ORDERS_PER_ASSIGNMENT.' orders.',
            ]);
        }

        if (! $driver->is_active) {
            throw ValidationException::withMessages(['driver_id' => 'This driver is not active.']);
        }

        return DB::transaction(function () use ($orderIds, $driver, $admin) {
            $driver->load('areas');

            $orders = Order::query()
                ->whereIn('id', $orderIds)
                ->lockForUpdate()
                ->with('delivery')
                ->get();

            $errors = [];

            if ($orders->count() !== count($orderIds)) {
                $errors[] = 'Some selected orders no longer exist.';
            }

            foreach ($orders as $order) {
                if (! in_array($order->status, self::ASSIGNABLE_STATUSES, true)) {
                    $errors[] = "{$order->order_number}: not ready for pickup (status: {$order->status->value}).";

                    continue;
                }

                if ($order->delivery && $order->delivery->status !== DeliveryStatus::FAILED) {
                    $errors[] = "{$order->order_number}: already assigned to a driver.";

                    continue;
                }

                if (! $this->driverCovers($driver, $order)) {
                    $errors[] = "{$order->order_number}: {$driver->full_name} does not cover {$order->barangay}, {$order->city}.";
                }
            }

            if ($errors) {
                throw ValidationException::withMessages(['order_ids' => $errors]);
            }

            $deliveries = collect();

            foreach ($orders as $order) {
                $delivery = $order->delivery ?? new OrderDelivery(['order_id' => $order->id]);

                $delivery->fill([
                    'driver_id' => $driver->id,
                    'assigned_by' => $admin->id,
                    'status' => DeliveryStatus::ASSIGNED,
                    'assigned_at' => now(),
                    'picked_up_at' => null,
                    'delivered_at' => null,
                    'failed_at' => null,
                    'failure_reason' => null,
                ])->save();

                $deliveries->push($delivery);
            }

            return $deliveries;
        });
    }

    /** Change driver while the parcel has not been picked up yet (or after a failed attempt). */
    public function reassign(OrderDelivery $delivery, Driver $driver, User $admin): OrderDelivery
    {
        return DB::transaction(function () use ($delivery, $driver, $admin) {
            $delivery = OrderDelivery::whereKey($delivery->id)->lockForUpdate()->with('order')->firstOrFail();

            if (! in_array($delivery->status, DeliveryStatus::reassignable(), true)) {
                throw ValidationException::withMessages([
                    'delivery' => 'The driver already picked up this order, it can no longer be reassigned.',
                ]);
            }

            if (! $driver->is_active) {
                throw ValidationException::withMessages(['driver_id' => 'This driver is not active.']);
            }

            $driver->load('areas');

            if (! $this->driverCovers($driver, $delivery->order)) {
                throw ValidationException::withMessages([
                    'driver_id' => "{$driver->full_name} does not cover {$delivery->order->barangay}, {$delivery->order->city}.",
                ]);
            }

            $delivery->update([
                'driver_id' => $driver->id,
                'assigned_by' => $admin->id,
                'status' => DeliveryStatus::ASSIGNED,
                'assigned_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ]);

            return $delivery;
        });
    }

    /** Take the order back to the "to dispatch" list. */
    public function unassign(OrderDelivery $delivery): void
    {
        DB::transaction(function () use ($delivery) {
            $delivery = OrderDelivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();

            if (! in_array($delivery->status, DeliveryStatus::reassignable(), true)) {
                throw ValidationException::withMessages([
                    'delivery' => 'The driver already picked up this order, it can no longer be unassigned.',
                ]);
            }

            $delivery->delete();
        });
    }

    public function driverCovers(Driver $driver, Order $order): bool
    {
        return $driver->areas->contains(fn (DriverArea $area) => $area->covers($order));
    }
}
