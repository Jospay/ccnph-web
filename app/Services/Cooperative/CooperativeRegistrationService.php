<?php

namespace App\Services\Cooperative;

use App\Mail\CooperativeRegistrationReceived;
use App\Models\CooperativeRegister;
use App\Models\User;
use App\Services\Movider\MoviderVerifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CooperativeRegistrationService
{
    private const OTP_COOLDOWN = 300;

    public function __construct(private MoviderVerifyService $movider) {}

    /**
     * Step 1: Save the registration (unverified) and send the OTP to the leader's phone.
     *
     * If an unverified registration already exists for the same leader email and/or phone
     * (or the one stored in this session), it is UPDATED instead of creating a new row.
     *
     * @param  array<string, mixed>  $data  validated request data (includes UploadedFile logo / cda_certificate)
     *
     * @throws Throwable
     */
    public function register(array $data, int|string|null $sessionCooperativeId = null): CooperativeRegister
    {
        $matches = CooperativeRegister::pendingMatching($data['email'], $data['contact'], $sessionCooperativeId);
        $existing = $matches->shift();

        $cooperative = $existing
            ? $this->updatePending($existing, $data)
            : $this->createPending($data);

        // Any other unverified rows that matched (e.g. email on one, phone on another)
        // are abandoned drafts of the same person.
        foreach ($matches as $duplicate) {
            $this->cancel($duplicate);
        }

        return $cooperative;
    }

    /**
     * Resend the OTP (respects the 5-minute cooldown).
     *
     * @throws Throwable
     */
    public function resendOtp(CooperativeRegister $cooperative): array
    {
        $wait = $this->secondsUntilResend($cooperative);

        if ($wait > 0) {
            return [
                'status' => 'pending',
                'retry_after' => $wait,
                'message' => "Please wait {$wait} seconds before requesting a new OTP.",
            ];
        }

        if ($cooperative->verification_request_id) {
            $this->cancelRequest($cooperative->verification_request_id);
        }

        $this->sendOtp($cooperative);

        return [
            'status' => 'otp_sent',
            'retry_after' => self::OTP_COOLDOWN,
            'message' => 'A new OTP has been sent to your mobile number.',
        ];
    }

    /**
     * Step 2: Verify the OTP, create the leader's web-admin account, move the
     * cooperative to "pending_approval" and email the leader.
     *
     * @throws Throwable
     */
    public function verify(CooperativeRegister $cooperative, string $otpCode): CooperativeRegister
    {
        if (! $cooperative->verification_request_id) {
            throw new \RuntimeException('No pending verification found. Please resend the code.', 404);
        }

        // Check conflicts BEFORE the OTP is consumed.
        $cdaTaken = CooperativeRegister::where('cda_registration_number', $cooperative->cda_registration_number)
            ->where('id', '!=', $cooperative->id)
            ->where('status', '!=', CooperativeRegister::STATUS_PENDING_VERIFICATION)
            ->exists();

        if ($cdaTaken) {
            throw new \RuntimeException('This CDA registration number has already been registered.', 422);
        }

        $accountTaken = User::where('email', $cooperative->leader_email)
            ->orWhere('phone', $cooperative->leader_phone)
            ->exists();

        if ($accountTaken) {
            throw new \RuntimeException('This email or mobile number is already registered.', 422);
        }

        $response = $this->movider->acknowledge($cooperative->verification_request_id, $otpCode);

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

        DB::transaction(function () use ($cooperative) {
            // The password is already hashed; User's `hashed` cast will not re-hash it.
            $user = User::create([
                'name' => trim($cooperative->leader_first_name.' '.$cooperative->leader_last_name),
                'email' => $cooperative->leader_email,
                'phone' => $cooperative->leader_phone,
                'password' => $cooperative->leader_password,
            ]);

            // TODO: assign the "cooperative admin" role/permission here if you use roles.

            $cooperative->update([
                'leader_user_id' => $user->id,
                'leader_password' => null,
                'verification_request_id' => null,
                'phone_verified_at' => now(),
                'status' => CooperativeRegister::STATUS_PENDING_APPROVAL,
            ]);
        });

        $cooperative->refresh();

        $this->discardPendingDuplicates($cooperative);
        $this->notifyRegistrationReceived($cooperative);

        return $cooperative;
    }

    /**
     * Abandon an unverified registration and clean up its files.
     */
    public function cancel(CooperativeRegister $cooperative): void
    {
        if ($cooperative->isPhoneVerified()) {
            return;
        }

        if ($cooperative->verification_request_id) {
            $this->cancelRequest($cooperative->verification_request_id);
        }

        Storage::disk('public')->delete($cooperative->logo_path);
        Storage::disk('local')->delete($cooperative->cda_certificate_path);
        $cooperative->delete();
    }

    public function secondsUntilResend(CooperativeRegister $cooperative): int
    {
        if (! $cooperative->otp_sent_at) {
            return 0;
        }

        $passed = (int) $cooperative->otp_sent_at->diffInSeconds(now());

        return max(self::OTP_COOLDOWN - $passed, 0);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    private function createPending(array $data): CooperativeRegister
    {
        $logoPath = $data['logo']->store('cooperatives/logos', 'public');
        $certPath = $data['cda_certificate']->store('cooperatives/certificates', 'local');

        try {
            $cooperative = CooperativeRegister::create([
                ...$this->attributesFrom($data),
                'logo_path' => $logoPath,
                'cda_certificate_path' => $certPath,
                'status' => CooperativeRegister::STATUS_PENDING_VERIFICATION,
            ]);

            $this->sendOtp($cooperative);
        } catch (Throwable $e) {
            // Don't leave orphaned files / rows if the SMS could not be sent.
            Storage::disk('public')->delete($logoPath);
            Storage::disk('local')->delete($certPath);
            isset($cooperative) && $cooperative->delete();

            throw $e;
        }

        return $cooperative;
    }

    /**
     * Update an unverified registration with the newly submitted details.
     * A new OTP is only sent when the phone changed, none was sent yet, or the cooldown has passed.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    private function updatePending(CooperativeRegister $cooperative, array $data): CooperativeRegister
    {
        $oldLogo = $cooperative->logo_path;
        $oldCert = $cooperative->cda_certificate_path;
        $previousRequestId = $cooperative->verification_request_id;
        $phoneChanged = $cooperative->leader_phone !== $data['contact'];

        $logoPath = $data['logo']->store('cooperatives/logos', 'public');
        $certPath = $data['cda_certificate']->store('cooperatives/certificates', 'local');

        try {
            // Not saved yet: if sending the OTP fails, nothing is persisted.
            $cooperative->fill([
                ...$this->attributesFrom($data),
                'logo_path' => $logoPath,
                'cda_certificate_path' => $certPath,
                'status' => CooperativeRegister::STATUS_PENDING_VERIFICATION,
            ]);

            $needsOtp = $phoneChanged
                || ! $previousRequestId
                || $this->secondsUntilResend($cooperative) === 0;

            if ($needsOtp) {
                if ($previousRequestId) {
                    $this->cancelRequest($previousRequestId);
                }

                $this->sendOtp($cooperative); // also saves the filled attributes
            } else {
                $cooperative->save();
            }
        } catch (Throwable $e) {
            Storage::disk('public')->delete($logoPath);
            Storage::disk('local')->delete($certPath);

            throw $e;
        }

        Storage::disk('public')->delete($oldLogo);
        Storage::disk('local')->delete($oldCert);

        return $cooperative;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFrom(array $data): array
    {
        return [
            'name' => $data['cooperative_name'],
            'acronym' => $data['acronym'] ?? null,
            'cda_registration_number' => $data['cda_registration_number'],
            'type' => $data['cooperative_type'],
            'email' => $data['coop_email'],
            'contact' => $data['coop_contact'],
            'region' => $data['region'] ?? '',
            'province' => $data['province'] ?? '',
            'city' => $data['city'] ?? '',
            'barangay' => $data['barangay'] ?? '',
            'street_address' => $data['street_address'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'leader_first_name' => $data['first_name'],
            'leader_last_name' => $data['last_name'],
            'leader_position' => $data['position'],
            'leader_email' => $data['email'],
            'leader_phone' => $data['contact'],
            'leader_password' => Hash::make($data['password']),
        ];
    }

    /**
     * @throws Throwable
     */
    private function sendOtp(CooperativeRegister $cooperative): void
    {
        $response = $this->movider->startVerification($cooperative->leader_phone);

        if (empty($response['request_id'])) {
            throw new \RuntimeException('Failed to send OTP. Please try again.', 500);
        }

        $cooperative->update([
            'verification_request_id' => $response['request_id'],
            'otp_sent_at' => now(),
        ]);
    }

    private function cancelRequest(string $requestId): void
    {
        try {
            $this->movider->cancel($requestId);
        } catch (Throwable $e) {
            report($e); // best effort: a failed cancel must not block the user
        }
    }

    /**
     * Once a cooperative is verified, other unverified drafts with the same CDA number are obsolete.
     */
    private function discardPendingDuplicates(CooperativeRegister $cooperative): void
    {
        CooperativeRegister::where('cda_registration_number', $cooperative->cda_registration_number)
            ->where('id', '!=', $cooperative->id)
            ->where('status', CooperativeRegister::STATUS_PENDING_VERIFICATION)
            ->get()
            ->each(fn (CooperativeRegister $draft) => $this->cancel($draft));
    }

    private function notifyRegistrationReceived(CooperativeRegister $cooperative): void
    {
        try {
            $mail = Mail::to($cooperative->leader_email);

            if ($cooperative->email && strcasecmp($cooperative->email, $cooperative->leader_email) !== 0) {
                $mail->cc($cooperative->email);
            }

            $mail->send(new CooperativeRegistrationReceived($cooperative));
        } catch (Throwable $e) {
            // The registration is already saved; a mail failure must not undo it.
            report($e);
        }
    }
}
