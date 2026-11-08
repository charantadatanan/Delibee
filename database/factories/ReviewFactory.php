<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_id' => Business::factory(),
            'product_id' => Product::factory(),
            'order_id' => null,
            'order_item_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => ReviewStatus::Published,
        ];
    }

    /**
     * A review attached to a delivered order item, which makes it a verified
     * purchase and lets it count towards BeeRank.
     */
    public function verified(): static
    {
        return $this->state(function (array $attributes): array {
            $order = Order::factory()->delivered()->create([
                'business_id' => $attributes['business_id'] ?? Business::factory(),
            ]);

            $item = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $attributes['product_id'] ?? Product::factory(),
            ]);

            return [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
            ];
        });
    }
}
