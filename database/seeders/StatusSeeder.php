<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'active'],
            ['id' => 2, 'name' => 'approved'],
            ['id' => 3, 'name' => 'rejected'],
            ['id' => 4, 'name' => 'cancelled'],
            ['id' => 5, 'name' => 'pending'],
            ['id' => 6, 'name' => 'finished'],
            ['id' => 7, 'name' => 'paid'],
            ['id' => 8, 'name' => 'unpaid'],
            ['id' => 9, 'name' => 'overdue'],
            ['id' => 10, 'name' => 'for_approval'],
            ['id' => 11, 'name' => 'success'],
            ['id' => 12, 'name' => 'failed'],
            ['id' => 13, 'name' => 'archived'],

            // For intellectual property schedules
            ['id' => 14, 'name' => 'waiting_for_payment'],
            ['id' => 15, 'name' => 'registered'],
            ['id' => 16, 'name' => 'expired'],
        ]);

    }
}
