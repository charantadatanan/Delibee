<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $words = fake()->unique()->words(3, true);
        $name = Str::title(is_string($words) ? $words : implode(' ', $words));

        return [
            'business_id' => Business::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->paragraph(),
            'price_minor' => fake()->numberBetween(50, 50000),
            'currency' => 'PHP',
            'stock' => fake()->numberBetween(5, 200),
            'status' => ProductStatus::Active,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Draft,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
