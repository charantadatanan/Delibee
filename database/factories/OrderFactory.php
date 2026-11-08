<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 100000);
        $shipping = fake()->numberBetween(0, 5000);

        return [
            'number' => 'DB-'.Str::upper(Str::random(10)),
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'currency' => 'PHP',
            'subtotal_minor' => $subtotal,
            'shipping_minor' => $shipping,
            'discount_minor' => 0,
            'total_minor' => $subtotal + $shipping,
            'shipping_address' => [
                'name' => fake()->name(),
                'line1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'postal_code' => (string) fake()->numberBetween(1000, 9999),
                'phone' => fake()->numerify('+63 ### ### ####'),
            ],
            'customer_note' => null,
            'ordered_at' => now(),
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
            'shipped_at' => now()->subDay(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'shipped_at' => now()->subDays(3),
            'delivered_at' => now()->subDay(),
        ]);
    }

    /**
     * Delivered, but after the promised delivery window, so it counts as late.
     */
    public function deliveredLate(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'shipped_at' => now()->subDays(12),
            'delivered_at' => now()->subDays(2),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now()->subDay(),
        ]);
    }
}
