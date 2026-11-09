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
                'slug' => 'cooperative-profit-sharing',
                'name' => 'Cooperative Profit Sharing',
                'description' => 'Funds distributed among cooperative owners or shareholders based on their respective ownership shares and the cooperative’s approved profit-sharing policy.',
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
            [
                'id' => 6,
                'slug' => 'point-back',
                'name' => 'Point Back',
                'description' => 'Points awarded to members for purchases paid using cooperative points, excluding Cash on Delivery (COD) and online payments.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'slug' => 'seller-earnings',
                'name' => 'Seller Earnings',
                'description' => 'Funds allocated to sellers from completed and paid orders, representing their sales revenue after applicable fees and deductions.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'slug' => 'online-payment-commission',
                'name' => 'Online Payment Commission',
                'description' => 'Commission earned by the cooperative from transactions paid through online payment methods, based on the applicable commission percentage.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['slug', 'name', 'description', 'updated_at']);
    }
}
