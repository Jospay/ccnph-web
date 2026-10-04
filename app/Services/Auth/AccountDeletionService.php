<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AccountDeletionService
{
    private const OTP_COOLDOWN_SECONDS = 300;

    public function __construct(private MoviderVerifyService $movider) {}

    /**
     * Step 1: Send OTP to confirm the deletion request.
     *
     * @throws Throwable
     */
    public function requestDeletion(User $user): array
    {
        if ($wait = $this->cooldownRemaining($user)) {
            return [
                'status' => 'pending',
                'retry_after' => $wait,
                'message' => 'OTP already sent. Please check your phone.',
            ];
        }

        $this->startOtp($user);

        return [
            'status' => 'otp_sent',
            'retry_after' => self::OTP_COOLDOWN_SECONDS,
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
            $code = $response['error']['code'] ?? null;

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
        if ($wait = $this->cooldownRemaining($user)) {
            return [
                'status' => 'pending',
                'retry_after' => $wait,
                'message' => "Please wait {$wait} seconds before requesting a new OTP.",
            ];
        }

        // Cancel the old Movider request (best effort — never block the resend)
        if ($user->deletion_verification_request_id) {
            try {
                $this->movider->cancel($user->deletion_verification_request_id);
            } catch (Throwable $e) {
                Log::warning('Movider cancel failed', ['message' => $e->getMessage()]);
            }
        }

        $this->startOtp($user);

        return [
            'status' => 'otp_sent',
            'retry_after' => self::OTP_COOLDOWN_SECONDS,
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

    /* ------------------------------------------------------------------ */
    /* Helpers */
    /* ------------------------------------------------------------------ */

    /**
     * Seconds left in the resend cooldown, or 0 if a new OTP may be sent.
     */
    private function cooldownRemaining(User $user): int
    {
        if (! $user->deletion_otp_sent_at) {
            return 0;
        }

        $secondsPassed = (int) $user->deletion_otp_sent_at->diffInSeconds(now());

        return max(0, self::OTP_COOLDOWN_SECONDS - $secondsPassed);
    }

    /**
     * Send the OTP and save the Movider request_id on the user.
     *
     * @throws \RuntimeException
     */
    private function startOtp(User $user): void
    {
        $to = $this->toMoviderPhone((string) $user->phone);

        try {
            $response = $this->movider->startVerification($to);
        } catch (ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);

            Log::error('Movider deletion OTP failed', ['body' => $body, 'to' => $to]);

            throw new \RuntimeException(
                'Could not send OTP. Please check your phone number and try again.',
                422
            );
        }

        if (empty($response['request_id'])) {
            Log::error('Movider deletion OTP: no request_id', ['response' => $response, 'to' => $to]);

            throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
        }

        $user->update([
            'deletion_verification_request_id' => $response['request_id'],
            'deletion_otp_sent_at' => now(),
            'deletion_token' => null,
        ]);
    }

    /**
     * users.phone is stored as 09XXXXXXXXX, but Movider needs 639XXXXXXXXX.
     * Used only in this flow, so registration is not affected.
     */
    private function toMoviderPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '09')) {
            return '63'.substr($digits, 1);
        }

        return $digits; // already 63...
    }
}
