<?php

namespace App\Http\Controllers\Web\DeliveryAdmin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\OrderDelivery;
use App\Services\Delivery\DeliveryAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Monitor assigned deliveries + reassign / unassign. */
class DeliveryController extends Controller
{
    public function __construct(protected DeliveryAssignmentService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'driver_id', 'city', 'search']);

        $deliveries = OrderDelivery::query()
            ->with([
                'driver:id,full_name,contact_number,photo',
                'order:id,order_number,recipient_name,recipient_phone,region,province,city,barangay,total,status,shop_id',
                'order.store:id,name',
            ])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['driver_id'] ?? null, fn ($q, $id) => $q->where('driver_id', $id))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->whereHas(
                'order', fn ($o) => $o->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower(trim($city))])
            ))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas(
                'order', fn ($o) => $o->where('order_number', 'like', "%{$s}%")
                    ->orWhere('recipient_name', 'like', "%{$s}%")
            ))
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('DeliveryAdmin/Deliveries/Index', [
            'deliveries' => $deliveries,
            'filters' => $filters,
            'statuses' => collect(DeliveryStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'drivers' => Driver::orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function reassign(Request $request, OrderDelivery $delivery): RedirectResponse
    {
        $data = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
        ]);

        $this->service->reassign($delivery, Driver::findOrFail($data['driver_id']), $request->user());

        return back()->with('success', 'Order reassigned.');
    }

    public function unassign(OrderDelivery $delivery): RedirectResponse
    {
        $this->service->unassign($delivery);

        return back()->with('success', 'Order returned to the dispatch list.');
    }
}
