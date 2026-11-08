<?php

namespace Database\Factories;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->paragraph(),
            'logo_path' => null,
            'currency' => 'PHP',
            'status' => BusinessStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BusinessStatus::Pending,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BusinessStatus::Suspended,
        ]);
    }

    /**
     * A business young enough to qualify for Rising Bees.
     */
    public function rising(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonths(2),
        ]);
    }
}
