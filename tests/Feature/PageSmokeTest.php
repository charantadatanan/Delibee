<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;

it('renders the public storefront pages', function () {
    $business = Business::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($business)->for($category)->create();

    $this->get(route('marketplace'))->assertOk();
    $this->get(route('categories.index'))->assertOk();
    $this->get(route('categories.show', $category))->assertOk();
    $this->get(route('businesses.show', $business))->assertOk();
    $this->get(route('products.show', [$business, $product->slug]))->assertOk();
    $this->get(route('ranking'))->assertOk();
});

it('renders every customer page', function () {
    $customer = User::factory()->create();

    foreach (['dashboard', 'cart.index', 'checkout', 'orders.index', 'rewards', 'chat.index'] as $route) {
        $this->actingAs($customer)->get(route($route))->assertOk();
    }
});

it('renders the seller pages for an approved seller', function () {
    $this->seed(RoleSeeder::class);

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    Business::factory()->for($seller, 'owner')->create();

    $this->actingAs($seller)->get(route('seller.products'))->assertOk();
    $this->actingAs($seller)->get(route('seller.orders'))->assertOk();
});

it('renders the onboarding and pending pages', function () {
    $this->seed(RoleSeeder::class);

    $newcomer = User::factory()->create();
    $this->actingAs($newcomer)->get(route('seller.onboarding'))->assertOk();

    $pendingSeller = User::factory()->create();
    $pendingSeller->assignRole('seller');

    Business::factory()->for($pendingSeller, 'owner')->pending()->create();

    $this->actingAs($pendingSeller)->get(route('seller.pending'))->assertOk();
});
