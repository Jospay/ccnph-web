<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendReactivationOtpRequest;
use App\Http\Requests\Auth\SendReactivationOtpRequest;
use App\Http\Requests\Auth\VerifyReactivationOtpRequest;
use App\Http\Resources\Api\User\ApiProfileResource;
use App\Services\Auth\ReactivationService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ReactivationController extends Controller
{
    public function __construct(private ReactivationService $reactivationService) {}

    /**
     * Send the reactivation OTP. Called only after the user
     * explicitly confirms they want to reactivate their account.
     */
    public function send(SendReactivationOtpRequest $request): JsonResponse
    {
        try {
            $retryAfter = $this->reactivationService->sendOtpForCredentials(
                $request->validated('phone'),
                $request->validated('password'),
            );

            return response()->json([
                'status' => 'otp_sent',
                'retry_after' => $retryAfter,
                'message' => 'A verification code has been sent to your phone number.',
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to send code.',
            ], 500);
        }
    }

    /**
     * Verify the OTP sent to reactivate a soft-deleted account.
     * On success this restores the account and logs the user in,
     * returning the same shape as a normal login response.
     */
    public function verify(VerifyReactivationOtpRequest $request): JsonResponse
    {
        try {
            $data = $this->reactivationService->verifyOtp(
                $request->validated('phone'),
                $request->validated('otp_code'),
            );

            return response()->json([
                'message' => 'Account reactivated successfully.',
                'token' => $data['token'],
                'token_type' => 'Bearer',
                'user' => new ApiProfileResource($data['user']),
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Verification failed.',
            ], 500);
        }
    }

    /**
     * Resend the reactivation OTP.
     */
    public function resend(ResendReactivationOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->reactivationService->resendOtp(
                $request->validated('phone'),
            );

            return response()->json($result);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to resend code.',
            ], 500);
        }
    }
}
