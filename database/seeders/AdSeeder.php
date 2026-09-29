<?php

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ads = [
            [
                'name' => 'adkoto',
                'image' => 'ads/adkoto.png',
                'link' => 'https://adkoto.com/about',
                'is_active' => true,
                'sort_banner' => 1,
            ],
            [
                'name' => 'bb88',
                'image' => 'ads/bb88.png',
                'link' => 'https://bb88advertising.com/',
                'is_active' => true,
                'sort_banner' => 2,
            ],
            [
                'name' => 'gpcf',
                'image' => 'ads/gpcf.png',
                'link' => 'https://www.greenhouseparadise.com/',
                'is_active' => true,
                'sort_banner' => 3,
            ],
            [
                'name' => 'migs',
                'image' => 'ads/migs.png',
                'link' => 'https://www.migsinc.com/',
                'is_active' => true,
                'sort_banner' => 4,
            ],
            [
                'name' => 'pateex',
                'image' => 'ads/pateex.png',
                'link' => 'https://pateex.com/',
                'is_active' => true,
                'sort_banner' => 5,
            ],
            [
                'name' => 'prepdi',
                'image' => 'ads/prepdi.png',
                'link' => 'https://philippinerealestateportal.com/',
                'is_active' => true,
                'sort_banner' => 6,
            ],
        ];

        foreach ($ads as $ad) {
            Ad::create($ad);
        }
    }
}
