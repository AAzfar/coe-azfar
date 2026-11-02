<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'sku' => fake()->unique()->bothify('FK-???-####'),
            'category_id' => Category::factory(),
            'price' => fake()->randomFloat(2, 1, 100),
            'stock' => fake()->numberBetween(1, 100),
            'unit' => fake()->randomElement(['kg', 'pack', 'bottle', 'piece', 'box']),
            'image_url' => null,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
