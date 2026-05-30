<?php

namespace Database\Seeders;

use App\Models\MemberShareCapital;
use App\Models\Payment;
use App\Models\ShareCapitalSetting;
use App\Models\Status;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShareCapitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = ShareCapitalSetting::getLatest();

        if (!$setting) {
            $this->command->error('No share capital setting found. Run ShareCapitalSettingSeeder first.');
            return;
        }

        $members = User::where('user_type_id', UserType::MEMBER)
            ->whereDoesntHave('shareCapital')
            ->get();

        foreach ($members as $member) {
            $shareCapital = MemberShareCapital::create([
                'user_id' => $member->id,
                'status_id' => Status::ACTIVE,
                'amount' => $setting->required_amount,
                'term_months' => 1,
            ]);

            // Create the single schedule
            $schedule = \App\Models\ShareCapitalSchedule::create([
                'member_share_capital_id' => $shareCapital->id,
                'status_id' => Status::PAID,
                'installment_no' => 1,
                'amount' => $setting->required_amount,
                'due_date' => now()->toDateString(),
            ]);

            // Payment on the schedule (not on share capital directly)
            Payment::create([
                'payable_type' => \App\Models\ShareCapitalSchedule::class,
                'payable_id' => $schedule->id,
                'payment_method_id' => 1,
                'status_id' => Status::PAID,
                'payment_date' => now(),
                'amount' => $setting->required_amount,
            ]);

            $this->command->info("Share capital seeded for User ID: {$member->id}");
        }
    }
}
