<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('allocations')->upsert([
            [
                'id' => 1,
                'slug' => 'member-returns',
                'name' => 'Member Returns',
                'description' => 'Funds distributed back to members as benefits and cooperative earnings sharing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'slug' => 'operations',
                'name' => 'Operations',
                'description' => 'Funds used for daily cooperative management, maintenance, and administration.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'slug' => 'community-projects',
                'name' => 'Community Projects',
                'description' => 'Funds allocated for community support programs and cooperative projects.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'slug' => 'emergency-reserve',
                'name' => 'Emergency Reserve',
                'description' => 'Saved funds for unexpected expenses and cooperative security.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'slug' => 'system-fee',
                'name' => 'System Fee',
                'description' => 'Funds collected to support system operations, platform maintenance, infrastructure, and technical services.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['slug', 'name', 'description', 'updated_at']);
    }
}
