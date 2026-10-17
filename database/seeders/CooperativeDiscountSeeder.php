<?php

namespace Database\Seeders;

use App\Models\CooperativeDiscount;
use Illuminate\Database\Seeder;

class CooperativeDiscountSeeder extends Seeder
{
    public function run(): void
    {
        // Membership discount
        CooperativeDiscount::updateOrCreate(
            ['module' => 'Membership'],
            [
                'type' => CooperativeDiscount::TYPE_PERCENTAGE,
                'discount_value' => 1,
            ],
        );

        // Shared Capital discount
        CooperativeDiscount::updateOrCreate(
            ['module' => 'Shared Capital'],
            [
                'type' => CooperativeDiscount::TYPE_PERCENTAGE,
                'discount_value' => 5,
            ],
        );

        // Point discount
        CooperativeDiscount::updateOrCreate(
            ['module' => 'Point'],
            [
                'type' => CooperativeDiscount::TYPE_PERCENTAGE,
                'discount_value' => 2,
            ],
        );
    }
}
