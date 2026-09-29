<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeDeletedUserContactInfo extends Command
{
    protected $signature = 'users:purge-contacts';

    protected $description = 'Clear email and phone numbers for users soft-deleted over 30 days ago';

    public function handle(): void
    {
        $cutoffDate = Carbon::now()->subDays(30);

        $count = User::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone');
            })
            ->update([
                'email' => null,
                'phone' => null,
                'deletion_token' => null,
                'deletion_verification_request_id' => null,
            ]);

        $this->info("Successfully cleared contact info for {$count} user(s).");
    }
}
