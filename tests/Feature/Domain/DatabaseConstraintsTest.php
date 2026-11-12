<?php

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('rejects a review rating outside one to five', function () {
    $user = User::factory()->create();
    $business = Business::factory()->create();

    expect(fn () => DB::table('reviews')->insert([
        'user_id' => $user->id,
        'business_id' => $business->id,
        'rating' => 6,
        'status' => 'published',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('rejects negative product stock', function () {
    $product = Product::factory()->make(['stock' => -1]);

    expect(fn () => $product->save())->toThrow(QueryException::class);
});

it('rejects zero quantity on an order item', function () {
    $order = Order::factory()->create();

    expect(fn () => DB::table('order_items')->insert([
        'order_id' => $order->id,
        'product_id' => null,
        'product_name' => 'Invalid item',
        'unit_price_minor' => 100,
        'quantity' => 0,
        'subtotal_minor' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('rejects zero quantity in the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    expect(fn () => DB::table('cart_items')->insert([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
