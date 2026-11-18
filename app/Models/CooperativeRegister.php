<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CooperativeRegister extends Model
{
    use HasFactory;

    public const STATUS_PENDING_VERIFICATION = 'pending_verification';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'acronym',
        'logo_path',
        'cda_registration_number',
        'type',
        'cda_certificate_path',
        'email',
        'contact',
        'region',
        'province',
        'city',
        'barangay',
        'street_address',
        'zip_code',
        'leader_first_name',
        'leader_last_name',
        'leader_position',
        'leader_email',
        'leader_phone',
        'leader_password',
        'verification_request_id',
        'otp_sent_at',
        'phone_verified_at',
        'status',
        'leader_user_id',
    ];

    protected $hidden = [
        'leader_password',
        'verification_request_id',
    ];

    protected function casts(): array
    {
        return [
            'otp_sent_at' => 'datetime',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Unverified registrations that belong to the same person: same leader email,
     * same leader phone, or the record already stored in this browser session.
     * The session's own record always comes first.
     *
     * @return Collection<int, static>
     */
    public static function pendingMatching(?string $email, ?string $phone, int|string|null $sessionId = null): Collection
    {
        $rows = static::query()
            ->where('status', self::STATUS_PENDING_VERIFICATION)
            ->where(function ($query) use ($email, $phone, $sessionId) {
                $query->whereRaw('1 = 0');

                if ($email) {
                    $query->orWhere('leader_email', $email);
                }

                if ($phone) {
                    $query->orWhere('leader_phone', $phone);
                }

                if ($sessionId) {
                    $query->orWhere('id', $sessionId);
                }
            })
            ->get();

        return $rows
            ->sortBy(fn (self $row) => $sessionId && $row->id == $sessionId ? 0 : 1)
            ->values();
    }
}
