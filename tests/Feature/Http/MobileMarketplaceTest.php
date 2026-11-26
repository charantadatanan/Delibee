<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;

it('renders the mobile marketplace sections alongside the desktop hero', function () {
    $business = Business::factory()->create(['name' => 'Sunrise Orchard']);
    $category = Category::factory()->create();
    Product::factory()->for($business)->for($category)->create(['name' => 'Golden Mangoes']);

    $response = $this->get(route('marketplace'));

    $response->assertOk()
        ->assertSee('Top Products')
        ->assertSee('Featured Shops')
        ->assertSee('Trending Now')
        ->assertSee('Sunrise Orchard')
        ->assertSee('Golden Mangoes')
        ->assertSee('Mobile navigation', escape: false)
        ->assertSee('Search product, businesses', escape: false)
        ->assertSee('Good sellers rise.');
});

it('shows featured shop product counts and the guest join call to action', function () {
    $featured = Business::factory()->create(['name' => 'Sunrise Orchard']);
    $category = Category::factory()->create();
    Product::factory()->for($featured)->for($category)->create(['name' => 'Golden Mangoes']);

    // An active business without products should not break the featured shops strip.
    Business::factory()->create(['name' => 'Quiet Hive']);

    $response = $this->get(route('marketplace'));

    $response->assertOk()
        ->assertSee('Featured Shops')
        ->assertSee('Sunrise Orchard')
        ->assertSee('1 product', escape: false)
        ->assertDontSee('1 products', escape: false)
        ->assertSee('Join deliBEE', escape: false);
});
