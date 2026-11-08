<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\RankSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RankSnapshot>
 */
class RankSnapshotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'period_type' => 'month',
            'period_start' => now()->startOfMonth()->toDateString(),
            'score' => fake()->randomFloat(4, 0, 100),
            'rank' => fake()->numberBetween(1, 50),
            'metrics' => [
                'rating' => fake()->randomFloat(4, 0, 1),
                'completion' => fake()->randomFloat(4, 0, 1),
                'on_time' => fake()->randomFloat(4, 0, 1),
                'response_time' => fake()->randomFloat(4, 0, 1),
                'orders' => fake()->numberBetween(5, 100),
            ],
            'created_at' => now(),
        ];
    }
}
