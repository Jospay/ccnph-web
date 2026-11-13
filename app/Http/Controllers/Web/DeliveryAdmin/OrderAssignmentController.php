<?php

namespace App\Http\Controllers\Web\DeliveryAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\AssignOrdersRequest;
use App\Models\Driver;
use App\Models\Order;
use App\Services\Delivery\DeliveryAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderAssignmentController extends Controller
{
    public function __construct(protected DeliveryAssignmentService $service) {}

    /**
     * The dispatch board: all orders ready for pickup (every shop), filterable by
     * region > province > city > barangay, with a per-city distribution summary.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['region', 'province', 'city', 'barangay', 'search']);

        $orders = $this->service->ordersQuery($filters, 'unassigned')
            ->with([
                'store:id,name',
                'items:id,order_id,product_name,variant_name,quantity',
                'checkout.payment',
                'delivery',
            ])
            ->withCount('items')
            ->orderBy('city')
            ->orderBy('barangay')
            ->oldest()
            ->paginate(25)
            ->withQueryString();

        $orders->getCollection()->transform(fn (Order $o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'shop' => $o->store?->name,
            'recipient_name' => $o->recipient_name,
            'recipient_phone' => $o->recipient_phone,
            'region' => $o->region,
            'province' => $o->province,
            'city' => $o->city,
            'barangay' => $o->barangay,
            'full_address' => collect([
                $o->unit_bldg_house, $o->street, $o->barangay, $o->city, $o->province, $o->region, $o->postal_code,
            ])->filter()->implode(', '),
            'total' => (float) $o->total,
            'is_cod' => $o->isCod(),
            'items_count' => $o->items_count,
            'packed_at' => $o->packed_at?->toIso8601String(),
            'previous_attempt_failed' => (bool) $o->delivery, // only failed ones can show up here
            'failure_reason' => $o->delivery?->failure_reason,
        ]);

        return Inertia::render('DeliveryAdmin/Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            // chips like "Angeles City (12)" - filter options for the current region/province
            'cityDistribution' => $this->service->cityDistribution($filters),
            'maxPerAssignment' => DeliveryAssignmentService::MAX_ORDERS_PER_ASSIGNMENT,
        ]);
    }

    /**
     * JSON for the "Assign driver" modal: only drivers that cover the chosen city.
     * GET /delivery-admin/eligible-drivers?city=Angeles City&province=Pampanga&barangay=
     * Pass barangay only when every selected order is in the same barangay.
     */
    public function eligibleDrivers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:150'],
            'province' => ['nullable', 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:150'],
        ]);

        $drivers = $this->service
            ->eligibleDrivers($data['city'], $data['province'] ?? null, $data['barangay'] ?? null)
            ->map(fn (Driver $d) => [
                'id' => $d->id,
                'full_name' => $d->full_name,
                'contact_number' => $d->contact_number,
                'photo_url' => $d->photo_url,
                'active_deliveries_count' => $d->active_deliveries_count,
                'areas' => $d->areas->map(fn ($a) => trim(($a->barangay ? $a->barangay.', ' : '').$a->city))->values(),
            ]);

        return response()->json(['success' => true, 'data' => ['drivers' => $drivers]]);
    }

    /** Assign the selected orders to one driver. */
    public function assign(AssignOrdersRequest $request): RedirectResponse
    {
        $driver = Driver::findOrFail($request->integer('driver_id'));

        $deliveries = $this->service->assign(
            $request->input('order_ids'),
            $driver,
            $request->user(),
        );

        return back()->with(
            'success',
            $deliveries->count().' order(s) assigned to '.$driver->full_name.'.'
        );
    }
}
