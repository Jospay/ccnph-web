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
            [
                'id' => 1,
                'allocation_id' => 1,
                'service_id' => 1,
                'priority' => 1,
                'type' => 'Percentage',
                'value' => 0.4000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'allocation_id' => 5,
                'service_id' => 1,
                'priority' => 2,
                'type' => 'Percentage',
                'value' => 0.6000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['allocation_id', 'service_id', 'priority', 'type', 'value', 'updated_at']);
    }
}
