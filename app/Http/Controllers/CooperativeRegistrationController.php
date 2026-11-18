<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cooperative\StoreCooperativeRegistrationRequest;
use App\Http\Requests\Cooperative\VerifyCooperativeRegistrationRequest;
use App\Models\CooperativeRegister;
use App\Services\Cooperative\CooperativeRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CooperativeRegistrationController extends Controller
{
    private const SESSION_KEY = 'cooperative_registration.id';

    private const DONE_FLASH_KEY = 'registration_done';

    public function __construct(private CooperativeRegistrationService $registration) {}

    public function create(Request $request): Response
    {
        $cooperative = $this->resolveSessionCooperative($request);

        // A verified record must never keep the session "in progress".
        if ($cooperative?->isPhoneVerified()) {
            $request->session()->forget(self::SESSION_KEY);
            $cooperative = null;
        }

        // Flash data lives for exactly one request, so the "done" modal shows once
        // and a refresh gives the user a clean form again.
        $done = (bool) $request->session()->get(self::DONE_FLASH_KEY, false);

        $step = match (true) {
            $done => 'done',
            $cooperative !== null => 'verify',
            default => 'form',
        };

        return Inertia::render('CooperativeRegister', [
            'step' => $step,
            'phoneLastFour' => $step === 'verify' ? substr($cooperative->leader_phone, -4) : null,
            'otpSentAt' => $step === 'verify' ? $cooperative->otp_sent_at?->toIso8601String() : null,
        ]);
    }

    public function store(StoreCooperativeRegistrationRequest $request): RedirectResponse
    {
        try {
            $cooperative = $this->registration->register(
                $request->validated(),
                $request->session()->get(self::SESSION_KEY),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['contact' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['contact' => 'Failed to send verification code. Please try again.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $cooperative->id);

        return back()->with('success', 'OTP sent to your mobile number.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $cooperative = $this->resolveSessionCooperative($request);

        if (! $cooperative || $cooperative->isPhoneVerified()) {
            return $this->expiredSession();
        }

        try {
            $result = $this->registration->resendOtp($cooperative);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['otp' => 'Failed to resend code. Please try again.']);
        }

        return back()->with($result['status'] === 'pending' ? 'info' : 'success', $result['message']);
    }

    public function verify(VerifyCooperativeRegistrationRequest $request): RedirectResponse
    {
        $cooperative = $this->resolveSessionCooperative($request);

        if (! $cooperative || $cooperative->isPhoneVerified()) {
            return $this->expiredSession();
        }

        try {
            $this->registration->verify($cooperative, $request->validated('otp_code'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp_code' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['otp_code' => 'Verification failed. Please try again.']);
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('cooperatives.register.create')
            ->with(self::DONE_FLASH_KEY, true)
            ->with('success', 'Mobile number verified. Your registration is now pending approval.');
    }

    /**
     * Explicitly abandon the unverified registration (no longer called by closing the dialog).
     */
    public function cancel(Request $request): RedirectResponse
    {
        $cooperative = $this->resolveSessionCooperative($request);

        if ($cooperative) {
            $this->registration->cancel($cooperative);
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('cooperatives.register.create');
    }

    private function resolveSessionCooperative(Request $request): ?CooperativeRegister
    {
        $id = $request->session()->get(self::SESSION_KEY);

        if (! $id) {
            return null;
        }

        $cooperative = CooperativeRegister::find($id);

        if (! $cooperative) {
            $request->session()->forget(self::SESSION_KEY);
        }

        return $cooperative;
    }

    private function expiredSession(): RedirectResponse
    {
        return redirect()->route('cooperatives.register.create')
            ->withErrors(['contact' => 'Your session expired. Please start over.']);
    }
}
