<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\StoreUserAddressRequest;
use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateUserAddressRequest;
use App\Http\Resources\Api\Store\UserAddressResource;
use App\Http\Resources\Api\User\ApiProfileResource;
use App\Models\UserAddress;
use App\Services\User\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Get profile.
     *
     * Returns the authenticated user's profile. Eager-loads status
     * and userType relationships to pre-fill all form fields.
     *
     * @tags Settings > Profile
     *
     * @response scenario="success" {
     *   "data": {
     *     "id": 1,
     *     "name": "Juan dela Cruz",
     *     "phone": "09171234567",
     *     "email": "juan@example.com",
     *     "gender": "male",
     *     "birthdate": "1990-01-15",
     *     "region": "NCR",
     *     "province": "Metro Manila",
     *     "city": "Quezon City",
     *     "barangay": "Bagong Silangan",
     *     "street": "123 Rizal St.",
     *     "postal_code": "1110",
     *     "avatar": "https://example.com/storage/avatars/abc.jpg",
     *     "valid_id_type": "passport",
     *     "valid_id_number": "P1234567A",
     *     "front_valid_id_picture": "https://example.com/storage/valid-ids/front.jpg",
     *     "back_valid_id_picture": "https://example.com/storage/valid-ids/back.jpg",
     *     "is_verified": true,
     *     "status": { "id": 1, "name": "Pending" },
     *     "user_type": { "id": 2, "name": "Agent" }
     *   }
     * }
     */
    public function show(Request $request): ApiProfileResource
    {
        $user = $request->user()->load([
            'status',
            'userType',
        ]);

        return new ApiProfileResource($user);
    }

    /**
     * Update profile.
     *
     * Submits all profile data. Must use multipart/form-data due to file uploads.
     * On success the user enters a 2–3 day approval window.
     *
     * @tags Settings > Profile
     *
     * @bodyParam name string required Full name of the user. Example: Juan dela Cruz
     * @bodyParam phone string optional Contact phone number. Example: +639171234567
     * @bodyParam email string optional Email address. Example: juan@example.com
     * @bodyParam gender string optional Gender of the user. Example: male
     * @bodyParam birthdate string optional Date of birth (YYYY-MM-DD). Example: 1990-01-15
     * @bodyParam region string optional Region. Example: NCR
     * @bodyParam province string optional Province. Example: Metro Manila
     * @bodyParam city string optional City or municipality. Example: Quezon City
     * @bodyParam barangay string optional Barangay. Example: Bagong Silangan
     * @bodyParam street string optional Street address. Example: 123 Rizal St.
     * @bodyParam postal_code string optional Postal code. Example: 1110
     * @bodyParam avatar file optional Profile avatar image (jpg/png). Max: 2MB.
     * @bodyParam valid_id_type string optional Type of government-issued ID. Example: passport
     * @bodyParam valid_id_number string optional ID number. Example: P1234567A
     * @bodyParam front_valid_id_picture file optional Front side of the ID image (jpg/png). Max: 2MB.
     * @bodyParam back_valid_id_picture file optional Back side of the ID image (jpg/png). Max: 2MB.
     *
     * @response scenario="success" {
     *   "success": true,
     *   "message": "Your account details have been completed. Please wait 2-3 days for approval. Updates will be sent to your email.",
     *   "data": {
     *     "id": 1,
     *     "name": "Juan dela Cruz",
     *     "phone": "+639171234567",
     *     "email": "juan@example.com",
     *     "gender": "male",
     *     "birthdate": "1990-01-15",
     *     "region": "NCR",
     *     "province": "Metro Manila",
     *     "city": "Quezon City",
     *     "barangay": "Bagong Silangan",
     *     "street": "123 Rizal St.",
     *     "postal_code": "1110",
     *     "avatar": "https://example.com/storage/avatars/abc.jpg",
     *     "valid_id_type": "passport",
     *     "valid_id_number": "P1234567A",
     *     "front_valid_id_picture": "https://example.com/storage/valid-ids/front.jpg",
     *     "back_valid_id_picture": "https://example.com/storage/valid-ids/back.jpg",
     *     "is_verified": true,
     *     "status": { "id": 1, "name": "Pending" },
     *     "user_type": { "id": 2, "name": "Agent" }
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->updateProfile($request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Your account details have been completed. Please wait 2-3 days for approval. Updates will be sent to your email.',
            'data' => new ApiProfileResource($updatedUser),
        ]);
    }

    /**
     * Change password.
     *
     * Verifies the user's current password then replaces it with a new one.
     * The new password must be confirmed and meet minimum security requirements (min 8 chars).
     *
     * @tags Settings > Profile
     *
     * @bodyParam current_password string required The user's existing password. Example: OldPass123
     * @bodyParam new_password string required New password, min 8 characters. Example: NewPass456
     * @bodyParam new_password_confirmation string required Must match new_password. Example: NewPass456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Password changed successfully."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Current password is incorrect."
     * }
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $success = $this->profileService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password,
        );

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Upload avatar.
     *
     * Accepts a single image file and replaces the user's current avatar.
     * Returns the new public storage URL.
     *
     * @tags Settings > Profile
     *
     * @bodyParam avatar file required Image file — jpg, png, gif. Max: 2MB.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Avatar updated.",
     *   "data": {
     *     "avatar": "https://example.com/storage/avatars/xyz.jpg"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "avatar": ["The avatar must be an image.", "The avatar must not be greater than 2048 kilobytes."]
     *   }
     * }
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $url = $this->profileService->updateAvatar(
            $request->user(),
            $request->file('avatar'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated.',
            'data' => [
                'avatar' => $url,
            ],
        ]);
    }

    /**
     * Get all addresses for the authenticated user.
     */
    public function getAddresses(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => UserAddressResource::collection($addresses),
        ]);
    }

    /**
     * Create a new user address.
     */
    public function address(StoreUserAddressRequest $request): JsonResponse
    {
        $address = $this->profileService->addAddress($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data' => new UserAddressResource($address),
        ], 201);
    }

    /**
     * Update an existing user address.
     */
    public function updateAddress(UpdateUserAddressRequest $request, UserAddress $userAddress): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($userAddress->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access.'], 403);
        }

        $updatedAddress = $this->profileService->updateAddress($userAddress, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => new UserAddressResource($updatedAddress),
        ]);
    }

    /**
     * Delete a user address.
     */
    public function deleteAddress(Request $request, UserAddress $userAddress): JsonResponse
    {
        if ($userAddress->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access.'], 403);
        }

        if ($userAddress->is_default) {
            return response()->json(['success' => false, 'message' => 'Cannot delete default address.'], 422);
        }

        $userAddress->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }
}
