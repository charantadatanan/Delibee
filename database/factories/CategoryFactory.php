<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = fake()->unique()->words(2, true);
        $name = is_string($words) ? $words : implode(' ', $words);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(),
            'icon' => null,
            'position' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
