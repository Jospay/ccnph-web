<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllocationServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('allocation_services')->upsert([
            // Membership
            [
                'allocation_id' => 1,
                'service_id' => 1,
                'priority' => 1,
                'type' => 'Percentage',
                'value' => 40.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'allocation_id' => 5,
                'service_id' => 1,
                'priority' => 2,
                'type' => 'Percentage',
                'value' => 60.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Store
            [
                'allocation_id' => 1,
                'service_id' => 3,
                'priority' => 1,
                'type' => 'PHP',
                'value' => 1.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'allocation_id' => 5,
                'service_id' => 3,
                'priority' => 2,
                'type' => 'PHP',
                'value' => 1.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'allocation_id' => 7,
                'service_id' => 3,
                'priority' => 4,
                'type' => 'Percentage',
                'value' => 100.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'allocation_id' => 8,
                'service_id' => 3,
                'priority' => 3,
                'type' => 'PHP',
                'value' => 1.0000,
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ], ['allocation_id', 'service_id'], [
            'priority',
            'type',
            'value',
            'updated_at',
        ]);
    }
}
