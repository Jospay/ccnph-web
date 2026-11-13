<?php

namespace App\Http\Controllers\Web\DeliveryAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\DriverStoreRequest;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DriverController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'city', 'status']);

        $drivers = Driver::query()
            ->with('areas')
            ->withCount(['activeDeliveries as active_deliveries_count'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                    ->orWhere('contact_number', 'like', "%{$s}%");
            }))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->whereHas(
                'areas', fn ($a) => $a->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower(trim($city))])
            ))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('DeliveryAdmin/Drivers/Index', [
            'drivers' => $drivers,
            'filters' => $filters,
        ]);
    }

    public function store(DriverStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $driver = Driver::create([
                'full_name' => $data['full_name'],
                'contact_number' => $data['contact_number'],
                'password' => $data['password'], // hashed by the model cast
                'photo' => $request->hasFile('photo')
                    ? $request->file('photo')->store('drivers/photos', 'public')
                    : null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $request->user()->id,
            ]);

            $this->syncAreas($driver, $data['areas']);
        });

        return back()->with('success', 'Driver registered successfully.');
    }

    public function show(Driver $driver): Response
    {
        $driver->load('areas');

        $recent = $driver->deliveries()
            ->with('order:id,order_number,recipient_name,city,barangay,status')
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return Inertia::render('DeliveryAdmin/Drivers/Show', [
            'driver' => $driver,
            'recentDeliveries' => $recent,
            'stats' => [
                'active' => $driver->activeDeliveries()->count(),
                'delivered' => $driver->deliveries()->where('status', 'delivered')->count(),
                'failed' => $driver->deliveries()->where('status', 'failed')->count(),
            ],
        ]);
    }

    /** Use POST (not PATCH) from the form so the photo file upload works, same pattern as your seller routes. */
    public function update(DriverStoreRequest $request, Driver $driver): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $driver) {
            $payload = [
                'full_name' => $data['full_name'],
                'contact_number' => $data['contact_number'],
            ];

            if (array_key_exists('is_active', $data)) {
                $payload['is_active'] = $data['is_active'];
            }

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            if ($request->hasFile('photo')) {
                if ($driver->photo) {
                    Storage::disk('public')->delete($driver->photo);
                }
                $payload['photo'] = $request->file('photo')->store('drivers/photos', 'public');
            }

            $driver->update($payload);

            $this->syncAreas($driver, $data['areas']);

            // new password or deactivated -> kick the driver out of the app
            if (! empty($data['password']) || ($payload['is_active'] ?? true) === false) {
                $driver->tokens()->delete();
            }
        });

        return back()->with('success', 'Driver updated successfully.');
    }

    public function toggleActive(Driver $driver): RedirectResponse
    {
        if ($driver->is_active && $driver->activeDeliveries()->exists()) {
            throw ValidationException::withMessages([
                'driver' => 'This driver still has active deliveries. Reassign them first.',
            ]);
        }

        $driver->update(['is_active' => ! $driver->is_active]);

        if (! $driver->is_active) {
            $driver->tokens()->delete();
        }

        return back()->with('success', $driver->is_active ? 'Driver activated.' : 'Driver deactivated.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        if ($driver->activeDeliveries()->exists()) {
            throw ValidationException::withMessages([
                'driver' => 'This driver still has active deliveries. Reassign them first.',
            ]);
        }

        $driver->tokens()->delete();
        $driver->delete(); // soft delete: delivery history keeps the driver name

        return redirect()->route('delivery-admin.drivers.index')->with('success', 'Driver removed.');
    }

    /** Replace the driver's areas with the submitted list. */
    private function syncAreas(Driver $driver, array $areas): void
    {
        $driver->areas()->delete();

        $rows = collect($areas)
            ->map(fn ($a) => [
                'region' => $a['region'],
                'region_code' => $a['region_code'] ?? null,
                'province' => $a['province'],
                'province_code' => $a['province_code'] ?? null,
                'city' => $a['city'],
                'city_code' => $a['city_code'] ?? null,
                'barangay' => ($a['barangay'] ?? null) ?: null,
                'barangay_code' => ($a['barangay_code'] ?? null) ?: null,
            ])
            ->unique(fn ($a) => mb_strtolower($a['city'].'|'.$a['barangay']))
            ->values()
            ->all();

        $driver->areas()->createMany($rows);
    }
}
