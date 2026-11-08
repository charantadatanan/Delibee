<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusEvent>
 */
class OrderStatusEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => OrderStatus::Confirmed,
            'note' => null,
            'created_by' => null,
            'created_at' => now(),
        ];
    }
}
