<?php

namespace Database\Factories;

use App\Models\ShareCapitalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShareCapitalSetting>
 */
class ShareCapitalSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'required_amount' => 800000,
            'allowed_term_months' => [1, 6, 12, 24],
        ];
    }
}
