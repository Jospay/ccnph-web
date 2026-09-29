<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ReactivationService
{
    public function __construct(private MoviderVerifyService $movider) {}

    /**
     * Send (or resend, respecting cooldown) an OTP to reactivate a
     * soft-deleted account. Returns seconds until the code can be
     * resent again.
     *
     * @throws Throwable
     */
    public function sendOtp(User $user): int
    {
        if ($user->deletion_otp_sent_at) {
            $secondsPassed = (int) $user->deletion_otp_sent_at->diffInSeconds(now());

            if ($secondsPassed < 300) {
                return 300 - $secondsPassed;
            }
        }

        if ($user->deletion_verification_request_id) {
            $this->movider->cancel($user->deletion_verification_request_id);
        }

        DB::transaction(function () use ($user) {
            $response = $this->movider->startVerification($user->phone);

            if (empty($response['request_id'])) {
                throw new \RuntimeException('Failed to send reactivation code. Please try again.', 500);
            }

            $user->update([
                'deletion_verification_request_id' => $response['request_id'],
                'deletion_otp_sent_at' => now(),
            ]);
        });

        return 300;
    }

    /**
     * Resend the reactivation OTP for a given phone number.
     *
     * @throws Throwable
     */
    public function resendOtp(string $phone): array
    {
        $user = $this->findPendingUser($phone);

        $retryAfter = $this->sendOtp($user);

        return [
            'status' => 'otp_sent',
            'retry_after' => $retryAfter,
            'message' => 'A new reactivation code has been sent to your phone number.',
        ];
    }

    /**
     * Verify the reactivation OTP, restore the account, and log the
     * user in — mirrors a normal successful login response.
     *
     * @throws Throwable
     */
    public function verifyOtp(string $phone, string $otpCode): array
    {
        $user = $this->findPendingUser($phone, requireOtpSent: true);

        $response = $this->movider->acknowledge(
            $user->deletion_verification_request_id,
            $otpCode
        );

        if (isset($response['error'])) {
            $code = $response['error']['code'];

            match ($code) {
                426 => throw new \RuntimeException('This code has already been used.', 422),
                421 => throw new \RuntimeException('Invalid code.', 422),
                422 => throw new \RuntimeException('Code has expired.', 422),
                423 => throw new \RuntimeException('Too many attempts. Request a new code.', 429),
                default => throw new \RuntimeException('Verification failed. Please try again.', 500),
            };
        }

        return DB::transaction(function () use ($user): array {
            $user->restore();
            $user->update([
                'deletion_requested_at' => null,
                'scheduled_deletion_at' => null,
                'deletion_verification_request_id' => null,
                'deletion_otp_sent_at' => null,
                'deletion_token' => null,
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user,
            ];
        });
    }

    /**
     * @throws \RuntimeException
     */
    private function findPendingUser(string $phone, bool $requireOtpSent = false): User
    {
        $normalizedPhone = str_starts_with($phone, '63') ? '0'.substr($phone, 2) : $phone;

        $user = User::onlyTrashed()->where('phone', $normalizedPhone)->first();

        $isPending = $user
            && $user->scheduled_deletion_at
            && $user->scheduled_deletion_at->isFuture()
            && (! $requireOtpSent || $user->deletion_verification_request_id);

        if (! $isPending) {
            throw new \RuntimeException('No pending reactivation found for this number.', 404);
        }

        return $user;
    }

    /**
     * Re-verify credentials, then send the reactivation OTP. This is
     * called only when the user explicitly taps "Okay" on the
     * reactivation prompt — never automatically during login.
     *
     * @throws \RuntimeException
     * @throws Throwable
     */
    public function sendOtpForCredentials(string $phone, string $password): int
    {
        $normalizedPhone = str_starts_with($phone, '63') ? '0'.substr($phone, 2) : $phone;

        $user = User::onlyTrashed()->where('phone', $normalizedPhone)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new \RuntimeException('No pending reactivation found for this number.', 404);
        }

        if (! $user->scheduled_deletion_at || $user->scheduled_deletion_at->isPast()) {
            throw new \RuntimeException('No pending reactivation found for this number.', 404);
        }

        return $this->sendOtp($user);
    }
}
