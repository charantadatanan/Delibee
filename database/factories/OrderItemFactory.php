<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(100, 20000);
        $quantity = fake()->numberBetween(1, 4);
        $words = fake()->words(3, true);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => Str::title(is_string($words) ? $words : implode(' ', $words)),
            'unit_price_minor' => $unitPrice,
            'quantity' => $quantity,
            'subtotal_minor' => $unitPrice * $quantity,
        ];
    }
}
