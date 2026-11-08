<?php

use App\Enums\OrderStatus;
use App\Enums\PointsReason;
use App\Enums\ProductStatus;
use App\Exceptions\CheckoutException;
use App\Models\Business;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RankSnapshot;
use App\Models\Review;
use App\Models\User;
use App\Services\BeeRankService;
use App\Services\CheckoutService;
use App\Services\PointsService;
use App\Services\RankingService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Mockery;

it('splits a multi-seller cart into one order per seller and decrements stock', function () {
    $customer = User::factory()->create();
    $category = Category::factory()->create();

    $businessA = Business::factory()->create();
    $businessB = Business::factory()->create();

    $productA = Product::factory()->for($businessA)->for($category)->create([
        'stock' => 10,
        'price_minor' => 10000,
    ]);
    $productB = Product::factory()->for($businessB)->for($category)->create([
        'stock' => 5,
        'price_minor' => 20000,
    ]);

    CartItem::create(['user_id' => $customer->id, 'product_id' => $productA->id, 'quantity' => 2]);
    CartItem::create(['user_id' => $customer->id, 'product_id' => $productB->id, 'quantity' => 1]);

    $orders = app(CheckoutService::class)->place($customer, [
        'name' => 'Cora Customer',
        'line1' => '12 Market Street',
        'city' => 'Quezon City',
        'postal_code' => '1100',
        'phone' => '+63 900 000 0000',
    ]);

    expect($orders)->toHaveCount(2)
        ->and($orders->pluck('business_id')->sort()->values()->all())
        ->toEqual(collect([$businessA->id, $businessB->id])->sort()->values()->all())
        ->and($productA->fresh()->stock)->toBe(8)
        ->and($productB->fresh()->stock)->toBe(4)
        ->and($customer->pointsBalance())->toBeGreaterThan(0)
        ->and(CartItem::where('user_id', $customer->id)->count())->toBe(0);
});

it('refuses an order that exceeds stock and rolls the whole checkout back', function () {
    $customer = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 99]);

    expect(fn () => app(CheckoutService::class)->place($customer, [
        'name' => 'Cora Customer',
        'line1' => '12 Market Street',
        'city' => 'Quezon City',
        'postal_code' => '1100',
        'phone' => '+63 900 000 0000',
    ]))->toThrow(CheckoutException::class);

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(5);
});

it('restores stock and reverses purchase points when an order is cancelled', function () {
    $customer = User::factory()->create();
    $business = Business::factory()->create();
    $product = Product::factory()->for($business)->create(['stock' => 8]);

    $order = Order::factory()->for($business)->for($customer)->create([
        'status' => OrderStatus::Pending,
        'subtotal_minor' => 20000,
        'shipping_minor' => 0,
        'discount_minor' => 0,
        'total_minor' => 20000,
    ]);

    OrderItem::factory()->for($order)->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price_minor' => 10000,
        'subtotal_minor' => 20000,
    ]);

    app(PointsService::class)->award(
        $customer,
        200,
        PointsReason::Purchase,
        Order::class,
        $order->id,
    );

    expect($customer->pointsBalance())->toBe(200);

    Livewire::actingAs($customer)
        ->test('pages::orders.show', ['order' => $order])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(10)
        ->and($customer->pointsBalance())->toBe(0);
});

it('rewards a review once, even when the rating is submitted again', function () {
    $customer = User::factory()->create();
    $business = Business::factory()->create();
    $product = Product::factory()->for($business)->create();

    $order = Order::factory()->delivered()->for($business)->for($customer)->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id]);

    Livewire::actingAs($customer)
        ->test('pages::orders.show', ['order' => $order])
        ->set("ratings.{$item->id}", 5)
        ->call('leaveReview', $item->id)
        ->assertHasNoErrors();

    expect(Review::where('order_item_id', $item->id)->count())->toBe(1)
        ->and($customer->pointsBalance())->toBe((int) config('commerce.review_reward_points'));

    Livewire::actingAs($customer)
        ->test('pages::orders.show', ['order' => $order])
        ->set("ratings.{$item->id}", 4)
        ->call('leaveReview', $item->id)
        ->assertHasNoErrors();

    expect(Review::where('order_item_id', $item->id)->count())->toBe(1)
        ->and($customer->pointsBalance())->toBe((int) config('commerce.review_reward_points'));
});

it('lets a seller advance an order one step but not move it backwards', function () {
    $this->seed(RoleSeeder::class);

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $business = Business::factory()->for($seller, 'owner')->create();
    $order = Order::factory()->for($business)->create(['status' => OrderStatus::Pending]);

    Livewire::actingAs($seller)
        ->test('pages::seller.orders')
        ->call('updateStatus', $order->number, OrderStatus::Confirmed->value)
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);

    Livewire::actingAs($seller)
        ->test('pages::seller.orders')
        ->call('updateStatus', $order->number, OrderStatus::Pending->value);

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

it('lets a seller toggle their own product and scopes the lookup to their shop', function () {
    $this->seed(RoleSeeder::class);

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $business = Business::factory()->for($seller, 'owner')->create();
    $product = Product::factory()->for($business)->create(['status' => ProductStatus::Active]);

    Livewire::actingAs($seller)
        ->test('pages::seller.products')
        ->call('toggle', $product->id)
        ->assertHasNoErrors();

    expect($product->fresh()->status)->toBe(ProductStatus::Draft);
});

it('hides products and storefronts of suspended businesses', function () {
    $business = Business::factory()->suspended()->create();
    $product = Product::factory()->for($business)->create();

    expect(Product::active()->whereKey($product->id)->exists())->toBeFalse();

    $this->get(route('businesses.show', $business))->assertNotFound();
});

it('evaluates only young businesses for rising bees', function () {
    $young = Business::factory()->rising()->create();
    Business::factory()->create([
        'created_at' => now()->subYears(2),
        'updated_at' => now()->subYears(2),
    ]);

    $beeRank = Mockery::mock(BeeRankService::class);
    $beeRank->shouldReceive('window')
        ->once()
        ->andReturn([now()->subDays(30), now()]);
    $beeRank->shouldReceive('evaluate')
        ->once()
        ->withArgs(fn (Business $business): bool => $business->is($young))
        ->andReturn(null);

    $rising = (new RankingService($beeRank))->risingBees();

    expect($rising)->toBeEmpty();
});

it('replaces a period when republished so stale ranks cannot survive', function () {
    $business = Business::factory()->create();

    RankSnapshot::create([
        'business_id' => $business->id,
        'period_type' => 'month',
        'period_start' => '2026-10-01',
        'score' => 50,
        'rank' => 1,
        'metrics' => [],
    ]);

    $snapshots = app(RankingService::class)->publish('month', Carbon::parse('2026-10-01'));

    expect($snapshots)->toBeEmpty()
        ->and(RankSnapshot::where('period_type', 'month')->count())->toBe(0);
});
