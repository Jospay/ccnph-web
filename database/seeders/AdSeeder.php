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
                'image' => 'ads/adkoto.jpg',
                'link' => 'https://adkoto.com/about',
                'is_active' => true,
                'sort_banner' => 1,
            ],
            [
                'name' => 'Summer Promo Banner',
                'image' => 'ads/bb88.jpg',
                'link' => 'https://bb88advertising.com/',
                'is_active' => true,
                'sort_banner' => 2,
            ],
            [
                'name' => 'New Arrival Collection',
                'image' => 'ads/chatkoto ads.jpg',
                'link' => 'https://chatkoto.com/',
                'is_active' => true,
                'sort_banner' => 3,
            ],
            [
                'name' => 'Flash Sale 50% Off',
                'image' => 'ads/gpcf.jpg',
                'link' => 'https://www.greenhouseparadise.com/',
                'is_active' => true,
                'sort_banner' => 4,
            ],
            [
                'name' => 'Expired Clearance Banner',
                'image' => 'ads/migs.jpg',
                'link' => 'https://www.migsinc.com/',
                'is_active' => true,
                'sort_banner' => 5,
            ],
            [
                'name' => 'Expired Clearance Banner',
                'image' => 'ads/prepdi.jpg',
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
