<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_number' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $driver = Driver::where('contact_number', $data['contact_number'])->first();

        if (! $driver || ! Hash::check($data['password'], $driver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid contact number or password.',
            ], 422);
        }

        if (! $driver->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your driver account is deactivated. Please contact the delivery admin.',
            ], 403);
        }

        // one active session per driver
        $driver->tokens()->delete();

        $driver->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $driver->createToken($data['device_name'] ?? 'driver-app')->plainTextToken,
                'driver' => $this->profile($driver),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['driver' => $this->profile($request->user())],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    private function profile(Driver $driver): array
    {
        $driver->loadMissing('areas');

        return [
            'id' => $driver->id,
            'full_name' => $driver->full_name,
            'contact_number' => $driver->contact_number,
            'photo_url' => $driver->photo_url,
            'areas' => $driver->areas->map(fn ($a) => [
                'region' => $a->region,
                'province' => $a->province,
                'city' => $a->city,
                'barangay' => $a->barangay,
            ])->values()->all(),
        ];
    }
}
