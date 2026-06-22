<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Models\IntellectualProperty;
use App\Models\IntellectualPropertyClaim;
use App\Models\IntellectualPropertyDocument;

class IntellectualPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = User::where('user_type_id', UserType::MEMBER)
            ->where('status_id', Status::REGISTERED)
            ->limit(3)
            ->get();

        // If not enough members exist, create them
        // if ($members->count() < 3) {
        //     $members = User::factory()->count(3)->create([
        //         'user_type_id' => UserType::MEMBER,
        //         'status_id' => Status::REGISTERED,
        //     ]);
        // }

        // 2. Loop through members and attach IP records
        $members->each(function ($user) {
            IntellectualProperty::factory()
                ->count(3)
                ->for($user)
                ->has(IntellectualPropertyClaim::factory()->count(3), 'claims')
                ->has(IntellectualPropertyDocument::factory()->count(3), 'documents')
                ->create([
                    'status_id' => Status::PENDING
                ]);
        });
    }
}
