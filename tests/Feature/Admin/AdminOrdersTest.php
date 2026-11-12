<?php

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('shows all orders to admins', function () {
    Order::factory()->create(['number' => 'DB-ADMIN-01']);

    $this->actingAs($this->admin)
        ->get(route('admin.orders'))
        ->assertOk()
        ->assertSee('DB-ADMIN-01');
});

it('forbids non-admins from the admin orders page', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.orders'))
        ->assertForbidden();
});

it('filters the order list by status', function () {
    Order::factory()->cancelled()->create(['number' => 'DB-CANCELLED']);
    Order::factory()->delivered()->create(['number' => 'DB-DELIVERED']);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.orders')
        ->set('statusFilter', 'cancelled')
        ->assertSee('DB-CANCELLED')
        ->assertDontSee('DB-DELIVERED');
});

it('filters the order list by business', function () {
    $first = Order::factory()->create(['number' => 'DB-FIRST']);
    Order::factory()->create(['number' => 'DB-SECOND']);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.orders')
        ->set('businessFilter', (string) $first->business_id)
        ->assertSee('DB-FIRST')
        ->assertDontSee('DB-SECOND');
});

it('lets an admin cancel a pending order', function () {
    $customer = User::factory()->create();
    $business = Business::factory()->create();
    $product = Product::factory()->for($business)->create(['stock' => 4]);

    $order = Order::factory()->for($business)->for($customer)->create(['status' => OrderStatus::Pending]);
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($this->admin)
        ->test('pages::orders.show', ['order' => $order])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(6);
});
