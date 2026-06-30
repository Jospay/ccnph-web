<?php

namespace App\Services\User;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\Status;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Summary of updateProfile
     * Handles the logic for updating a user's profile, including storing valid ID pictures.
     *
     * @return User|null
     */
    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        // 1. Get validated data (excludes files not in the rules)
        $data = $request->validated();

        // 2. Handle Front ID Photo
        if ($request->hasFile('front_valid_id_picture')) {
            $data['front_valid_id_picture'] = $this->storeIdPhoto(
                $request->file('front_valid_id_picture')
            );
        } else {
            // Remove it from the update array so we don't overwrite the existing path with null
            unset($data['front_valid_id_picture']);
        }

        // 3. Handle Back ID Photo
        if ($request->hasFile('back_valid_id_picture')) {
            $data['back_valid_id_picture'] = $this->storeIdPhoto(
                $request->file('back_valid_id_picture')
            );
        } else {
            unset($data['back_valid_id_picture']);
        }

        // 4. Update Status logic
        if ($user->user_type_id === UserType::BASIC) {
            $data['status_id'] = Status::FOR_APPROVAL;
        }

        // 5. Perform the update
        $user->update($data);

        return $user->fresh();
    }

    /**
     * Summary of storeIdPhoto
     * Handles the logic for storing valid ID photos and returns the storage path.
     *
     * @return bool|string
     */
    private function storeIdPhoto(UploadedFile $file): string
    {
        return $file->store('valid_ids', 'public');
    }

    /**
     * Summary of changePassword
     * Handles the logic for changing a user's password, including verifying the current password and hashing the new password.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // revoke tokens
        // $user->tokens()->delete();

        return true;
    }

    /**
     * Summary of updateAvatar
     * Handles the logic for updating a user's avatar, including deleting the old avatar if it exists and storing the new avatar.
     */
    public function updateAvatar(User $user, UploadedFile $avatar): string
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $avatar->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return asset('storage/'.$path);
    }

    public function addAddress(User $user, array $data): UserAddress
    {
        return DB::transaction(function () use ($user, $data) {
            if (! empty($data['is_default']) && $data['is_default'] == true) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create($data);
        });
    }

    public function updateAddress(UserAddress $address, array $data): UserAddress
    {
        return DB::transaction(function () use ($address, $data) {
            if (! empty($data['is_default']) && $data['is_default'] == true) {
                UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
            }

            $address->update($data);

            return $address->refresh();
        });
    }
}
