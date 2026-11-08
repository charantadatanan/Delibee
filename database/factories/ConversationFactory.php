<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'first_reply_at' => null,
            'last_message_at' => null,
        ];
    }

    /**
     * A conversation the seller has already answered, so it contributes to
     * the chat response time component of BeeRank.
     */
    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_reply_at' => now()->subHours(3),
            'last_message_at' => now()->subHours(3),
        ]);
    }
}
