<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CooperativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cooperatives = [
            [
                'name' => 'Rice-Up Irrigators and Farmers Agricultural Cooperative',
                'primary_color' => '#00A859',
                'secondary_color' => '#DDE9D1',
                'logo' => 'coop-logo/rifac.png',
            ],
            [
                'name' => 'Cooperatives Cooperation Network Philippines',
                'primary_color' => '#3E4093',
                'secondary_color' => '#CDE7D5',
                'logo' => 'coop-logo/ccnph.png',
            ],
            // [
            //     'name' => 'BB 88 Advertising & Digital Solutions Inc',
            //     'primary_color' => '#458C72',
            //     'secondary_color' => '#AACE46',
            //     'logo' => 'coop-logo/bb88.png',
            // ],
        ];

        foreach ($cooperatives as $coop) {
            DB::table('cooperatives')->updateOrInsert(
                ['name' => $coop['name']],
                [
                    'slug' => Str::slug($coop['name']),
                    'logo' => $coop['logo'],
                    'primary_color' => $coop['primary_color'],
                    'secondary_color' => $coop['secondary_color'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
