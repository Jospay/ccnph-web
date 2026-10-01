<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Http\Resources\Api\Cooperative\ApiCooperativeBrandingResource;
use App\Http\Resources\Api\User\ApiProfileResource;
use App\Notifications\GeneralNotification;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(private RegistrationService $registrationService) {}

    /**
     * Register — Step 1.
     *
     * Accept name, first_name, middle_name, last_name, cooperative_id + phone, then send an OTP via Movider.
     *
     * @tags Auth
     *
     * @unauthenticated
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->initiateRegistration(
                $request->validated()
            );

            return response()->json($result, $result['status'] === 'pending' ? 200 : 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            Log::error('Registration failed', ['exception' => $e]);

            return response()->json([
                'message' => 'Registration failed.',
            ], 500);
        }
    }

    /**
     * Register — Step 3.
     *
     * Set password and create the User record. Requires a valid
     * verification token obtained from Step 2 (OTP verification).
     *
     * @tags Auth
     *
     * @unauthenticated
     *
     * @response 201 {
     *   "message": "Registration complete.",
     *   "token": "1|abc123...",
     *   "token_type": "Bearer",
     *   "user": { "data": { "id": "1", "type": "users", "attributes": {} } },
     *   "cooperative": {
     *     "primary_color": "#3E4093",
     *     "secondary_color": "#F59E0B",
     *     "logo": "https://example.com/storage/logos/coop.png"
     *   }
     * }
     * @response 403 { "message": "Invalid or expired verification token." }
     * @response 404 { "message": "No pending registration found for this phone." }
     * @response 500 { "message": "Failed to complete registration." }
     */
    public function setPassword(SetPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->completeRegistration(
                $request->validated('phone'),
                $request->validated('password'),
                $request->validated('verification_token'),
            );

            $user = $result['user'];

            $user->notify(new GeneralNotification(
                type: 'registration_completed',
                title: 'Registration Successful!',
                body: 'Your account has been created. You can now complete your profile setup.',
                actionType: 'VIEW_PROFILE',
                route: '/profile',
                extraData: [
                    'user_id' => $user->id,
                    'status' => 'registered',
                ]
            ));

            return response()->json([
                'message' => 'Registration complete.',
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'user' => new ApiProfileResource($user),
                'cooperative' => ApiCooperativeBrandingResource::forUser($user),
            ], 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            Log::error('Registration completion failed', ['exception' => $e]);

            return response()->json([
                'message' => 'Failed to complete registration.',
            ], 500);
        }
    }
}
