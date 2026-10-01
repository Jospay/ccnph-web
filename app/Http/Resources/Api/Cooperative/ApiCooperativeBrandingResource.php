<?php

namespace App\Http\Resources\Api\Cooperative;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCooperativeBrandingResource extends JsonResource
{
    /**
     * Build the branding resource for a user's cooperative (or null).
     */
    public static function forUser(User $user): ?self
    {
        $coop = $user->loadMissing('cooperative')->cooperative;

        return $coop ? new self($coop) : null;
    }

    public function toArray(Request $request): array
    {
        return [
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'logo' => $this->logo ? asset('storage/'.$this->logo) : null,
        ];
    }
}
