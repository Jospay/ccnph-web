<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IdentifyDeletionRequest;
use App\Http\Requests\User\VerifyDeletionRequest;
use App\Models\User;
use App\Services\Auth\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AccountDeletionController extends Controller
{
    private const SESSION_USER_KEY = 'account_deletion.user_id';

    private const SESSION_TOKEN_KEY = 'account_deletion.token';

    public function __construct(private AccountDeletionService $deletionService) {}

    public function edit(Request $request): Response
    {
        $user = $this->resolveSessionUser($request);

        return Inertia::render('DeleteAccount', [
            'identified' => (bool) $user,
            'phoneLastFour' => $user?->phone ? substr($user->phone, -4) : null,
            'otpSentAt' => $user?->deletion_otp_sent_at?->toIso8601String(),
            'verified' => $request->session()->has(self::SESSION_TOKEN_KEY),
        ]);
    }

    public function identify(IdentifyDeletionRequest $request): RedirectResponse
    {
        $phone = $request->validated('phone');
        $user = User::where('phone', $phone)->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return back()->withErrors(['phone' => 'These credentials do not match our records.']);
        }

        try {
            $result = $this->deletionService->requestDeletion($user);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['phone' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['phone' => 'Failed to send verification code. Please try again.']);
        }

        // Only mark the session "identified" once the OTP has actually gone out.
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_USER_KEY, $user->id);

        return back()->with($result['status'] === 'pending' ? 'info' : 'success', $result['message']);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget([self::SESSION_USER_KEY, self::SESSION_TOKEN_KEY]);

        return redirect()->route('account-deletion.edit');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $user = $this->resolveSessionUser($request);

        if (! $user) {
            return $this->expiredSession();
        }

        try {
            $result = $this->deletionService->resendOtp($user);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['otp' => 'Failed to resend code. Please try again.']);
        }

        return back()->with($result['status'] === 'pending' ? 'info' : 'success', $result['message']);
    }

    public function verify(VerifyDeletionRequest $request): RedirectResponse
    {
        $user = $this->resolveSessionUser($request);

        if (! $user) {
            return $this->expiredSession();
        }

        try {
            $token = $this->deletionService->verifyDeletion($user, $request->validated('otp_code'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp_code' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['otp_code' => 'Verification failed. Please try again.']);
        }

        // Server-side only — never exposed to the client.
        $request->session()->put(self::SESSION_TOKEN_KEY, $token);

        return back()->with('success', 'Phone verified. Confirm below to finish deleting your account.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $this->resolveSessionUser($request);
        $token = $request->session()->get(self::SESSION_TOKEN_KEY);

        if (! $user || ! $token) {
            return $this->expiredSession();
        }

        try {
            $this->deletionService->completeDeletion($user, $token);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['deletion' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['deletion' => 'Failed to complete account deletion. Please try again.']);
        }

        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('account-deletion.edit')
            ->with('success', 'Your account has been scheduled for deletion.');
    }

    private function resolveSessionUser(Request $request): ?User
    {
        $id = $request->session()->get(self::SESSION_USER_KEY);

        return $id ? User::find($id) : null;
    }

    private function expiredSession(): RedirectResponse
    {
        return redirect()->route('account-deletion.edit')
            ->withErrors(['phone' => 'Your session expired. Please start over.']);
    }
}
