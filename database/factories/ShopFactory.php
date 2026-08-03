<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Shop::class; 

    public function definition(): array
    {
        $name = fake()->unique()->company();

        // Get an existing SELLER without a shop
        $seller = User::where('user_type_id', UserType::MEMBER)
            ->where('status_id', Status::ACTIVE)
            ->where('is_seller', true)
            ->whereDoesntHave('shop')
            ->first();

        // If none exist, create one
        if (!$seller) {
            $seller = User::factory()->create([
                'user_type_id' => UserType::MEMBER,
                'is_seller' => true
            ]);
        }

        return [
            'user_id' => $seller->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_official' => fake()->boolean(20),
        ];
    }
}
