<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CompleteDeletionRequest;
use App\Http\Requests\User\VerifyDeletionRequest;
use App\Services\Auth\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AccountDeletionController extends Controller
{
    public function __construct(private AccountDeletionService $deletionService) {}

    /**
     * Request account deletion — sends OTP to the authenticated user's phone.
     *
     * @tags Settings > Profile > Deletion
     */
    public function requestDeletion(Request $request): JsonResponse
    {
        try {
            $result = $this->deletionService->requestDeletion($request->user());

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to initiate account deletion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify OTP, receive deletion token.
     *
     * @tags Settings > Profile > Deletion
     */
    public function verifyDeletion(VerifyDeletionRequest $request): JsonResponse
    {
        try {
            $token = $this->deletionService->verifyDeletion(
                $request->user(),
                $request->validated('otp_code'),
            );

            return response()->json([
                'message' => 'Phone verified. Your account deletion has been scheduled.',
                'deletion_token' => $token,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Verification failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend deletion OTP.
     *
     * @tags Settings > Profile > Deletion
     */
    public function resendDeletionOtp(Request $request): JsonResponse
    {
        try {
            $result = $this->deletionService->resendOtp($request->user());

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to resend OTP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete deletion — soft-deletes the account, starts the 30-day grace period.
     *
     * @tags Settings > Profile > Deletion
     */
    public function completeDeletion(CompleteDeletionRequest $request): JsonResponse
    {
        try {
            $this->deletionService->completeDeletion(
                $request->user(),
                $request->validated('deletion_token'),
            );

            return response()->json([
                'message' => 'Your account has been scheduled for deletion.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to complete account deletion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
