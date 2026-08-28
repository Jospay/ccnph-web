<?php

namespace App\Services\Auth;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class RegistrationService
{
    public function __construct(private MoviderVerifyService $movider)
    {
    }

    /**
     * Step 1: Store in pending_registrations and send OTP via Movider.
     *
     * @throws Throwable
     */
    public function initiateRegistration(array $data): array
    {
        $phone = $data['phone'];
        $normalizedPhone = str_starts_with($phone, '63') ? '0' . substr($phone, 2) : $phone;
        $existingUser = User::where('phone', $normalizedPhone)->exists();

        if ($existingUser) {
            throw new \RuntimeException('An account with this phone number already exists.', 409);
        }

        $pending = PendingRegistration::where('phone', $phone)->first();

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

        DB::transaction(function () use ($data, $phone) {
            $pending = PendingRegistration::updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => $data['name'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'cooperative_id' => $data['cooperative_id'],
                    'phone_verified' => false,
                    'verification_token' => null,
                    'verification_request_id' => null,
                ]
            );

            $response = $this->movider->startVerification($phone);

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
     * Step 2: Verify OTP via Movider, return a verification token for step 3.
     *
     * @throws Throwable
     */
    public function verifyPhone(string $phone, string $otpCode): string
    {
        $pending = PendingRegistration::where('phone', $phone)->first();

        if (!$pending || !$pending->verification_request_id) {
            throw new \RuntimeException('No pending registration found.', 404);
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
     * Step 3: Set password and create the real User record.
     *
     * @throws Throwable
     */
    public function completeRegistration(string $phone, string $password, string $verificationToken): array
    {
        $pending = PendingRegistration::where('phone', $phone)
            ->where('phone_verified', true)
            ->first();

        if (!$pending || !Hash::check($verificationToken, $pending->verification_token)) {
            throw new \RuntimeException('Invalid or expired verification token.', 403);
        }

        return DB::transaction(function () use ($pending, $password): array {
            $formattedPhone = $pending->phone;
            if (str_starts_with($formattedPhone, '63')) {
                $formattedPhone = '0' . substr($formattedPhone, 2);
            }

            $user = User::create([
                'name' => $pending->name,
                'first_name' => $pending->first_name,
                'middle_name' => $pending->middle_name,
                'last_name' => $pending->last_name,
                'cooperative_id' => $pending->cooperative_id,
                'phone' => $formattedPhone,
                'phone_verified_at' => now(),
            ]);

            $user->password = $password;
            $user->save();

            event(new Registered($user));

            $token = $user->createToken('auth-token')->plainTextToken;

            $pending->delete();

            return [
                'token' => $token,
                'user' => $user,
            ];
        });
    }

    /**
     * Resend OTP to the given phone number.
     *
     * @throws Throwable
     */
    public function resendOtp(string $phone): array
    {
        $pending = PendingRegistration::where('phone', $phone)
            ->where('phone_verified', false)
            ->first();

        if (!$pending) {
            throw new \RuntimeException('No pending registration found for this number.', 404);
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

        // Cancel old Movider request if exists
        if ($pending->verification_request_id) {
            $this->movider->cancel($pending->verification_request_id);
        }

        DB::transaction(function () use ($pending) {
            $response = $this->movider->startVerification($pending->phone);

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
}
