<?php

use App\Models\Business;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Livewire\Livewire;

function seedMixedCurrencyCart(User $customer): void
{
    $phpBusiness = Business::factory()->create(['currency' => 'PHP']);
    $usdBusiness = Business::factory()->create(['currency' => 'USD']);

    $phpProduct = Product::factory()->for($phpBusiness)->create([
        'price_minor' => 10000,
        'currency' => 'PHP',
        'stock' => 10,
    ]);
    $usdProduct = Product::factory()->for($usdBusiness)->create([
        'price_minor' => 2000,
        'currency' => 'USD',
        'stock' => 10,
    ]);

    CartItem::create(['user_id' => $customer->id, 'product_id' => $phpProduct->id, 'quantity' => 1]);
    CartItem::create(['user_id' => $customer->id, 'product_id' => $usdProduct->id, 'quantity' => 1]);
}

it('shows cart subtotals per currency instead of one combined total', function () {
    $customer = User::factory()->create();
    seedMixedCurrencyCart($customer);

    Livewire::actingAs($customer)
        ->test('pages::cart')
        ->assertSee('Subtotal (PHP)')
        ->assertSee('Subtotal (USD)')
        ->assertSee(Money::format(10000, 'PHP'))
        ->assertSee(Money::format(2000, 'USD'))
        ->assertDontSee(Money::format(12000, 'PHP'));
});

it('shows checkout totals per currency instead of one combined total', function () {
    $customer = User::factory()->create();
    seedMixedCurrencyCart($customer);

    Livewire::actingAs($customer)
        ->test('pages::checkout')
        ->assertSee('Total (PHP)')
        ->assertSee('Total (USD)')
        ->assertSee(Money::format(15000, 'PHP'))
        ->assertDontSee(Money::format(22000, 'PHP'));
});
