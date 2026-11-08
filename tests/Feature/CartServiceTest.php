<?php

use App\Models\Business;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;

it('merges a repeated add into one cart line', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);
    $cart = app(CartService::class);

    $cart->add($user, $product, 2);
    $cart->add($user, $product, 3);

    expect(CartItem::where('user_id', $user->id)->count())->toBe(1)
        ->and($cart->quantityOf($user, $product))->toBe(5);
});

it('clamps a cart quantity to available stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $cart = app(CartService::class);

    $item = $cart->add($user, $product, 2);
    $cart->updateQuantity($user, $item, 99);

    expect($item->fresh()->quantity)->toBe(5);
});

it('removes a cart line when its quantity reaches zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $cart = app(CartService::class);

    $item = $cart->add($user, $product, 2);
    $cart->updateQuantity($user, $item, 0);

    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);
});

it('rejects adding an out-of-stock product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->outOfStock()->create();

    expect(fn () => app(CartService::class)->add($user, $product))
        ->toThrow(RuntimeException::class);
});

it('groups cart subtotals by currency', function () {
    $user = User::factory()->create();
    $phpBusiness = Business::factory()->create(['currency' => 'PHP']);
    $usdBusiness = Business::factory()->create(['currency' => 'USD']);
    $phpProduct = Product::factory()->for($phpBusiness)->create([
        'price_minor' => 10000,
        'currency' => 'PHP',
        'stock' => 5,
    ]);
    $usdProduct = Product::factory()->for($usdBusiness)->create([
        'price_minor' => 2000,
        'currency' => 'USD',
        'stock' => 5,
    ]);

    $cart = app(CartService::class);
    $cart->add($user, $phpProduct, 1);
    $cart->add($user, $usdProduct, 1);

    expect($cart->subtotalsByCurrency($user))->toBe([
        'PHP' => 10000,
        'USD' => 2000,
    ]);
});

it('refuses to modify a cart line belonging to another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    $item = app(CartService::class)->add($owner, $product, 2);

    expect(fn () => app(CartService::class)->updateQuantity($intruder, $item, 1))
        ->toThrow(RuntimeException::class);
});
