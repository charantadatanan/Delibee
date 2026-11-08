<?php

namespace Database\Factories;

use App\Enums\PointsReason;
use App\Models\PointsLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointsLedgerEntry>
 */
class PointsLedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'delta' => fake()->numberBetween(10, 500),
            'reason' => PointsReason::Purchase->value,
            'reference_type' => null,
            'reference_id' => null,
            'created_at' => now(),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'delta' => -fake()->numberBetween(10, 500),
            'reason' => PointsReason::Redemption->value,
        ]);
    }
}
