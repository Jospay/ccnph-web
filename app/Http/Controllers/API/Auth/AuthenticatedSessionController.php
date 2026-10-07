<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\AccountPendingReactivationException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\Cooperative\ApiCooperativeBrandingResource;
use App\Http\Resources\Api\User\ApiProfileResource;
use App\Models\UserAuthDevice;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Store a newly authenticated user session via phone + password.
     *
     * @tags Authentication
     */
    public function store(LoginRequest $request, AuthenticationService $authService): JsonResponse
    {
        try {
            $data = $authService->login($request);

            return response()->json([
                'message' => 'Login successful.',
                'token' => $data['token'],
                'token_type' => 'Bearer',
                'user' => new ApiProfileResource($data['user']),
                'cooperative' => ApiCooperativeBrandingResource::forUser($data['user']),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => $e->errors(),
            ], 422);

        } catch (AccountPendingReactivationException $e) {
            return response()->json([
                'status' => 'pending_reactivation',
                'message' => $e->getMessage(),
                'phone' => $e->phone,
            ], 409);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (\Exception) {
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    /**
     * Authenticate user via biometric device.
     *
     * @tags Authentication
     */
    public function biometricLogin(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => ['required', 'string', 'max:255'],
                'public_key' => ['required', 'string'],
            ]);

            $authDevice = UserAuthDevice::where('device_id', $validated['device_id'])
                ->where('public_key', $validated['public_key'])
                ->where('biometric_enabled', true)
                ->first();

            if (! $authDevice) {
                return response()->json([
                    'message' => 'Device not registered or biometric authentication not enabled.',
                ], 401);
            }

            // Include soft-deleted user attached to this device
            $user = $authDevice->user()->withTrashed()->first();

            if (! $user) {
                return response()->json([
                    'message' => 'User account not found.',
                ], 404);
            }

            // Check if user is soft-deleted and pending reactivation
            if ($user->trashed()) {
                if ($user->scheduled_deletion_at && $user->scheduled_deletion_at->isFuture()) {
                    throw new AccountPendingReactivationException(
                        $user->phone,
                        'Your account is scheduled for deletion. Would you like to reactivate it? You can log in manually with your phone number and password.'
                    );
                }

                return response()->json([
                    'message' => 'User account has been deleted.',
                ], 410);
            }

            if (! $user->phone_verified_at) {
                return response()->json([
                    'message' => 'User account is not verified.',
                ], 403);
            }

            $authDevice->update(['last_used_at' => now()]);

            $token = $user->createToken('biometric-auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Biometric login successful.',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new ApiProfileResource($user),
                'cooperative' => ApiCooperativeBrandingResource::forUser($user),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (AccountPendingReactivationException $e) {
            return response()->json([
                'status' => 'pending_reactivation',
                'message' => $e->getMessage(),
                'phone' => $e->phone,
            ], 409);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Biometric login failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @tags Authentication
     */
    public function destroy(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
