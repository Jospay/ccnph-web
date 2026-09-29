<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AccountDeletionService
{
    public function __construct(private MoviderVerifyService $movider) {}

    /**
     * Step 1: Send OTP to confirm the deletion request.
     *
     * @throws Throwable
     */
    public function requestDeletion(User $user): array
    {
        if ($user->deletion_otp_sent_at) {
            $secondsPassed = (int) $user->deletion_otp_sent_at->diffInSeconds(now());

            if ($secondsPassed < 300) {
                $wait = 300 - $secondsPassed;

                return [
                    'status' => 'pending',
                    'retry_after' => $wait,
                    'message' => 'OTP already sent. Please check your phone.',
                ];
            }
        }

        DB::transaction(function () use ($user) {
            $response = $this->movider->startVerification($user->phone);

            if (empty($response['request_id'])) {
                throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
            }

            $user->update([
                'deletion_verification_request_id' => $response['request_id'],
                'deletion_otp_sent_at' => now(),
                'deletion_token' => null,
            ]);
        });

        return [
            'status' => 'otp_sent',
            'retry_after' => 300,
            'message' => 'OTP sent to your phone number.',
        ];
    }

    /**
     * Step 2: Verify OTP, issue a deletion token.
     *
     * @throws Throwable
     */
    public function verifyDeletion(User $user, string $otpCode): string
    {
        if (! $user->deletion_verification_request_id) {
            throw new \RuntimeException('No pending deletion request found. Please request deletion again.', 404);
        }

        $response = $this->movider->acknowledge(
            $user->deletion_verification_request_id,
            $otpCode
        );

        if (isset($response['error'])) {
            $code = $response['error']['code'];

            match ($code) {
                426 => throw new \RuntimeException('This OTP has already been used.', 422),
                421 => throw new \RuntimeException('Invalid OTP code.', 422),
                422 => throw new \RuntimeException('OTP has expired.', 422),
                423 => throw new \RuntimeException('Too many attempts. Request a new OTP.', 429),
                default => throw new \RuntimeException('Verification failed. Please try again.', 500),
            };
        }

        $deletionToken = Str::random(64);

        $user->update([
            'deletion_verification_request_id' => null,
            'deletion_token' => Hash::make($deletionToken),
            'deletion_requested_at' => now(),
        ]);

        return $deletionToken;
    }

    /**
     * Resend OTP for account deletion.
     *
     * @throws Throwable
     */
    public function resendOtp(User $user): array
    {
        if ($user->deletion_otp_sent_at) {
            $secondsPassed = (int) $user->deletion_otp_sent_at->diffInSeconds(now());

            if ($secondsPassed < 300) {
                $wait = 300 - $secondsPassed;

                return [
                    'status' => 'pending',
                    'retry_after' => $wait,
                    'message' => "Please wait {$wait} seconds before requesting a new OTP.",
                ];
            }
        }

        if ($user->deletion_verification_request_id) {
            $this->movider->cancel($user->deletion_verification_request_id);
        }

        DB::transaction(function () use ($user) {
            $response = $this->movider->startVerification($user->phone);

            if (empty($response['request_id'])) {
                throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
            }

            $user->update([
                'deletion_verification_request_id' => $response['request_id'],
                'deletion_otp_sent_at' => now(),
                'deletion_token' => null,
            ]);
        });

        return [
            'status' => 'otp_sent',
            'retry_after' => 300,
            'message' => 'A new OTP has been sent to your phone number.',
        ];
    }

    /**
     * Step 3: Soft-delete the account and start the 30-day grace period.
     *
     * @throws Throwable
     */
    public function completeDeletion(User $user, string $deletionToken): void
    {
        if (! $user->deletion_token || ! Hash::check($deletionToken, $user->deletion_token)) {
            throw new \RuntimeException('Invalid or expired deletion token.', 403);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'scheduled_deletion_at' => now()->addDays(30),
                'deletion_token' => null,
            ]);

            $user->tokens()->delete();
            $user->delete(); // soft delete
        });
    }
}
