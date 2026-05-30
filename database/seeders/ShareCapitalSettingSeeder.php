<?php

namespace Database\Seeders;

use App\Models\ShareCapitalSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShareCapitalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShareCapitalSetting::firstOrCreate(
            [],
            [
                'required_amount' => 800000,
                'allowed_term_months' => [1, 3, 6, 12, 24],
            ]
        );
    }
}
