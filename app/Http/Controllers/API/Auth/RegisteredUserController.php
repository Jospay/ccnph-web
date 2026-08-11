<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Notifications\GeneralNotification;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(private RegistrationService $registrationService) {}

    /**
     * Register — Step 1.
     *
     * Accept name + phone, then send an OTP via Movider.
     *
     * @tags Auth
     *
     * @unauthenticated
     *
     * @response 201 scenario="New registration" {
     *   "status": "created",
     *   "message": "OTP sent.",
     *   "phone": "+639171234567"
     * }
     * @response 200 scenario="Pending registration" {
     *   "status": "pending",
     *   "message": "A pending registration already exists. OTP resent.",
     *   "phone": "+639171234567"
     * }
     * @response 422 {
     *   "message": "The phone has already been taken.",
     *   "errors": { "phone": ["The phone has already been taken."] }
     * }
     * @response 500 { "message": "Registration failed." }
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->initiateRegistration(
                $request->validated('name'),
                $request->validated('phone'),
            );

            return response()->json($result, $result['status'] === 'pending' ? 200 : 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
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
     *   "user": {
     *     "id": 1,
     *     "name": "Juan dela Cruz",
     *     "phone": "+639171234567"
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

            $result['user']->notify(new GeneralNotification(
                type: 'registration_completed',
                title: 'Registration Successful!',
                body: 'Your account has been created. You can now complete your profile setup.',
                actionType: 'VIEW_PROFILE',
                route: '/profile',
                extraData: [
                    'user_id' => $result['user']->id,
                    'status' => 'registered',
                ]
            ));

            return response()->json([
                'message' => 'Registration complete.',
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'user' => $result['user'],
            ], 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to complete registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
