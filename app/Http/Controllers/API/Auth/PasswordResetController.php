<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordReset\ForgotPasswordRequest;
use App\Http\Requests\PasswordReset\ResetPasswordRequest;
use App\Http\Requests\PasswordReset\ResendOtpRequest;
use App\Http\Requests\PasswordReset\VerifyOtpRequest;
use App\Services\PasswordReset\PasswordResetService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $service)
    {
    }

    /**
     * Forgot Password — Step 1.
     *
     * Accept phone number, normalize it, and send an OTP via Movider.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 scenario="OTP sent" {
     *   "status": "otp_sent",
     *   "retry_after": 300,
     *   "message": "OTP sent to your phone number."
     * }
     * @response 200 scenario="Cooldown active" {
     *   "status": "pending",
     *   "retry_after": 243,
     *   "message": "OTP already sent. Please check your phone."
     * }
     * @response 500 { "message": "Failed to initiate password reset." }
     */
    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->service->initiateReset($request->validated('phone'));

            return response()->json($result);

        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->httpCode($e->getCode()));
        } catch (Throwable $e) {
            return response()->json(['message' => 'Failed to initiate password reset.'], 500);
        }
    }

    /**
     * Forgot Password — Step 2.
     *
     * Verify OTP and receive a verification token for the reset step.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 {
     *   "message": "OTP verified. Please reset your password.",
     *   "verification_token": "abc123...",
     *   "phone": "09171234567"
     * }
     * @response 422 { "message": "Invalid OTP code." }
     * @response 404 { "message": "No password reset request found." }
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $token = $this->service->verifyOtp(
                $request->validated('phone'),
                $request->validated('otp_code'),
            );

            return response()->json([
                'message' => 'OTP verified. Please reset your password.',
                'verification_token' => $token,
                'phone' => $request->validated('phone'),
            ]);

        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->httpCode($e->getCode()));
        } catch (Throwable $e) {
            return response()->json(['message' => 'Verification failed.'], 500);
        }
    }

    /**
     * Forgot Password — Step 3.
     *
     * Reset the password using the verification token from Step 2.
     *
     * @tags Auth
     * @unauthenticated
     *
     * @response 200 { "message": "Password reset successfully." }
     * @response 403 { "message": "Invalid or expired verification token." }
     * @response 404 { "message": "User not found." }
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->service->resetPassword(
                $request->validated('phone'),
                $request->validated('password'),
                $request->validated('verification_token'),
            );

            return response()->json(['message' => 'Password reset successfully.']);

        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->httpCode($e->getCode()));
        } catch (Throwable $e) {
            return response()->json(['message' => 'Failed to reset password.'], 500);
        }
    }

    /**
     * Resend OTP.
     *
     * @tags Auth
     * @unauthenticated
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->service->resendOtp($request->validated('phone'));

            return response()->json($result);

        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->httpCode($e->getCode()));
        } catch (Throwable $e) {
            return response()->json(['message' => 'Failed to resend OTP.'], 500);
        }
    }

    /**
     * Ensure the code is a valid HTTP status code, fallback to 500.
     */
    private function httpCode(mixed $code): int
    {
        $int = (int) $code;
        return $int >= 100 && $int <= 599 ? $int : 500;
    }
}
