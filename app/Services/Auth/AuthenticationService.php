<?php

namespace App\Services\Auth;

use App\Exceptions\AccountPendingReactivationException;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Authenticate a user via phone + password and issue a token.
     *
     * @throws ValidationException
     * @throws AccountPendingReactivationException
     */
    public function login(LoginRequest $request): array
    {
        $this->guardAgainstPendingDeletion(
            $request->validated('phone'),
            $request->validated('password'),
        );

        $request->authenticate();

        $user = $request->user();

        if (! $user->phone_verified_at) {
            throw new \RuntimeException('Phone number is not verified.', 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * If the credentials match a soft-deleted account still within its
     * grace period, interrupt the login. This ONLY detects the state —
     * it does not send any OTP. The OTP is sent separately, only after
     * the user explicitly confirms they want to reactivate.
     *
     * @throws AccountPendingReactivationException
     */
    private function guardAgainstPendingDeletion(string $phone, string $password): void
    {
        $normalizedPhone = str_starts_with($phone, '63') ? '0'.substr($phone, 2) : $phone;

        $user = User::onlyTrashed()->where('phone', $normalizedPhone)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return;
        }

        if (! $user->scheduled_deletion_at || $user->scheduled_deletion_at->isPast()) {
            return;
        }

        throw new AccountPendingReactivationException(
            $user->phone,
            'Your account is scheduled for deletion. Would you like to reactivate it?'
        );
    }

    /**
     * Revoke the user's current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
