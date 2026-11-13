<?php

namespace App\Http\Controllers\API\Driver;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Driver\DriverDeliveryResource;
use App\Models\OrderDelivery;
use App\Services\Delivery\DriverDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverDeliveryController extends Controller
{
    public function __construct(protected DriverDeliveryService $service) {}

    private const WITH = [
        'order.store:id,name',
        'order.items:id,order_id,product_name,variant_name,quantity',
        'order.checkout.payment',
    ];

    /**
     * GET /api/driver/deliveries?tab=active|history&city=&search=
     * active  = assigned (to pick up) + picked_up (out for delivery)
     * history = delivered + failed
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tab' => ['sometimes', 'in:active,history'],
            'city' => ['sometimes', 'nullable', 'string', 'max:150'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $driver = $request->user();
        $tab = $data['tab'] ?? 'active';

        $statuses = $tab === 'history'
            ? [DeliveryStatus::DELIVERED, DeliveryStatus::FAILED]
            : DeliveryStatus::active();

        $paginator = OrderDelivery::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', $statuses)
            ->when($data['city'] ?? null, fn ($q, $city) => $q->whereHas(
                'order', fn ($o) => $o->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower(trim($city))])
            ))
            ->when($data['search'] ?? null, fn ($q, $s) => $q->whereHas(
                'order', fn ($o) => $o->where('order_number', 'like', "%{$s}%")
                    ->orWhere('recipient_name', 'like', "%{$s}%")
            ))
            ->with(self::WITH)
            ->orderByDesc($tab === 'history' ? 'updated_at' : 'assigned_at')
            ->paginate(20);

        $counts = OrderDelivery::where('driver_id', $driver->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'success' => true,
            'data' => [
                'deliveries' => DriverDeliveryResource::collection($paginator)->toArray($request),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
                'badges' => [
                    'to_pickup' => (int) ($counts[DeliveryStatus::ASSIGNED->value] ?? 0),
                    'out_for_delivery' => (int) ($counts[DeliveryStatus::PICKED_UP->value] ?? 0),
                    'delivered' => (int) ($counts[DeliveryStatus::DELIVERED->value] ?? 0),
                    'failed' => (int) ($counts[DeliveryStatus::FAILED->value] ?? 0),
                ],
            ],
        ]);
    }

    public function show(Request $request, OrderDelivery $delivery): JsonResponse
    {
        $this->ownDelivery($request, $delivery);

        $delivery->load(self::WITH);

        return response()->json([
            'success' => true,
            'data' => ['delivery' => (new DriverDeliveryResource($delivery))->toArray($request)],
        ]);
    }

    /** Driver collected the parcel at the shop. */
    public function pickup(Request $request, OrderDelivery $delivery): JsonResponse
    {
        $this->ownDelivery($request, $delivery);

        $delivery = $this->service->pickUp($delivery, $request->user());

        return $this->respond($request, $delivery, 'Order picked up. It is now out for delivery.');
    }

    /** Driver handed the parcel to the customer. */
    public function deliver(Request $request, OrderDelivery $delivery): JsonResponse
    {
        $this->ownDelivery($request, $delivery);

        $data = $request->validate([
            'proof' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $delivery = $this->service->deliver(
            $delivery,
            $request->user(),
            $request->file('proof'),
            $data['remarks'] ?? null,
        );

        return $this->respond($request, $delivery, 'Order marked as delivered.');
    }

    /** Could not deliver (customer unreachable, wrong address, refused...). */
    public function fail(Request $request, OrderDelivery $delivery): JsonResponse
    {
        $this->ownDelivery($request, $delivery);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $delivery = $this->service->fail($delivery, $request->user(), $data['reason']);

        return $this->respond($request, $delivery, 'Delivery attempt reported as failed.');
    }

    private function ownDelivery(Request $request, OrderDelivery $delivery): void
    {
        abort_unless($delivery->driver_id === $request->user()->id, 404, 'Delivery not found.');
    }

    private function respond(Request $request, OrderDelivery $delivery, string $message): JsonResponse
    {
        $delivery->load(self::WITH);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ['delivery' => (new DriverDeliveryResource($delivery))->toArray($request)],
        ]);
    }
}
