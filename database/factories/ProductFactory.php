<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(4, true);

        // Get an existing shop or return if none
        $shop = Shop::inRandomOrder()->first();
        if (!$shop) {
            throw new Exception('No shops found');
        }

        return [
            'shop_id' => $shop->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'description' => fake()->paragraph(),
            'views' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Attach categories AFTER product creation
     */
    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $categoryIds = Category::inRandomOrder()
                ->limit(rand(1, 3))
                ->pluck('id');

            $product->categories()->attach($categoryIds);

            $variants = ProductVariant::factory(rand(2, 4))
                ->create([
                    'product_id' => $product->id,
                ]);

            $variants->first()->update([
                'is_default' => true,
            ]);

            $attributes = Attribute::with('values')->get();

            foreach ($variants as $variant) {
                foreach ($attributes as $attribute) {
                    // pick random value for each attribute
                    $value = $attribute->values->random();
                    $variant->attributeValues()->attach($value->id);
                }
            }
        });
    }
}