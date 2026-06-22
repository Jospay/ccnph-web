<?php

namespace App\Services\PasswordReset;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetService
{
    public function __construct(private MoviderVerifyService $movider)
    {
    }

    /**
     * Step 1: Normalize phone, find user, send OTP via Movider.
     *
     * @throws Throwable
     */
    public function initiateReset(string $phone): array
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $user = User::where('phone', $normalizedPhone)->first();

        if (!$user) {
            return [
                'status' => 'otp_sent',
                'retry_after' => 300,
                'message' => 'If this number is registered, an OTP has been sent.',
            ];
        }

        $pending = PasswordResetRequest::where('phone', $normalizedPhone)->first();

        // Resend cooldown — 5 minutes
        if ($pending?->otp_sent_at) {
            $secondsPassed = (int) $pending->otp_sent_at->diffInSeconds(now());

            if ($secondsPassed < 300) {
                $wait = 300 - $secondsPassed;
                return [
                    'status' => 'pending',
                    'retry_after' => $wait,
                    'message' => 'OTP already sent. Please check your phone.',
                ];
            }
        }

        DB::transaction(function () use ($normalizedPhone) {
            $pending = PasswordResetRequest::updateOrCreate(
                ['phone' => $normalizedPhone],
                [
                    'phone_verified' => false,
                    'verification_token' => null,
                    'verification_request_id' => null,
                ]
            );

            $response = $this->movider->startVerification($this->toMoviderFormat($normalizedPhone));

            if (empty($response['request_id'])) {
                throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
            }

            $pending->update([
                'verification_request_id' => $response['request_id'],
                'otp_sent_at' => now(),
            ]);
        });

        return [
            'status' => 'otp_sent',
            'retry_after' => 300,
            'message' => 'OTP sent to your phone number.',
        ];
    }

    /**
     * Step 2: Verify OTP via Movider, return a short-lived verification token.
     *
     * @throws Throwable
     */
    public function verifyOtp(string $phone, string $otpCode): string
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $pending = PasswordResetRequest::where('phone', $normalizedPhone)->first();

        if (!$pending || !$pending->verification_request_id) {
            throw new \RuntimeException('No password reset request found.', 404);
        }

        $response = $this->movider->acknowledge(
            $pending->verification_request_id,
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

        $verificationToken = Str::random(64);

        $pending->update([
            'phone_verified' => true,
            'verification_request_id' => null,
            'verification_token' => Hash::make($verificationToken),
        ]);

        return $verificationToken;
    }

    /**
     * Step 3: Validate token and update the user's password.
     *
     * @throws Throwable
     */
    public function resetPassword(string $phone, string $password, string $verificationToken): void
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $pending = PasswordResetRequest::where('phone', $normalizedPhone)
            ->where('phone_verified', true)
            ->first();

        if (!$pending || !Hash::check($verificationToken, $pending->verification_token)) {
            throw new \RuntimeException('Invalid or expired verification token.', 403);
        }

        DB::transaction(function () use ($normalizedPhone, $password, $pending) {
            $user = User::where('phone', $normalizedPhone)->firstOrFail();
            $user->update(['password' => Hash::make($password)]);
            $pending->delete();
        });
    }

    /**
     * Resend OTP for an existing reset request.
     *
     * @throws Throwable
     */
    public function resendOtp(string $phone): array
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $pending = PasswordResetRequest::where('phone', $normalizedPhone)
            ->where('phone_verified', false)
            ->first();

        if (!$pending) {
            throw new \RuntimeException('No pending password reset found for this number.', 404);
        }

        if ($pending->otp_sent_at) {
            $secondsPassed = (int) $pending->otp_sent_at->diffInSeconds(now());

            if ($secondsPassed < 300) {
                $wait = 300 - $secondsPassed;
                return [
                    'status' => 'pending',
                    'retry_after' => $wait,
                    'message' => "Please wait {$wait} seconds before requesting a new OTP.",
                ];
            }
        }

        if ($pending->verification_request_id) {
            $this->movider->cancel($pending->verification_request_id);
        }

        DB::transaction(function () use ($pending) {
            $response = $this->movider->startVerification($this->toMoviderFormat($pending->phone));

            if (empty($response['request_id'])) {
                throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
            }

            $pending->update([
                'verification_request_id' => $response['request_id'],
                'otp_sent_at' => now(),
                'verification_token' => null,
            ]);
        });

        return [
            'status' => 'otp_sent',
            'retry_after' => 300,
            'message' => 'A new OTP has been sent to your phone number.',
        ];
    }

    /**
     * Normalize PH phone numbers to local format (09XXXXXXXXX).
     * Mirrors what RegistrationService does for User::where('phone').
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // +639XXXXXXXXX or 639XXXXXXXXX → 09XXXXXXXXX
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

    /**
     * Format localized format to international country code without symbol for Movider API (639XXXXXXXXX).
     */
    private function toMoviderFormat(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // 09XXXXXXXXX → 639XXXXXXXXX
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '63' . substr($digits, 1);
        }

        return $digits;
    }
}
