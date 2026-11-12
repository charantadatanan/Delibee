<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/marketplace')->name('home');

// Public marketplace.
Route::get('marketplace', [StorefrontController::class, 'marketplace'])->name('marketplace');
Route::get('categories', [StorefrontController::class, 'categories'])->name('categories.index');
Route::get('categories/{category}', [StorefrontController::class, 'category'])->name('categories.show');
Route::get('businesses/{business}', [StorefrontController::class, 'business'])->name('businesses.show');
Route::get('businesses/{business}/products/{product}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('ranking', [StorefrontController::class, 'ranking'])->name('ranking');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'pages.dashboard')->name('dashboard');

    // Honey Pot and checkout.
    Route::livewire('cart', 'pages::cart')->name('cart.index');
    Route::livewire('checkout', 'pages::checkout')->name('checkout');

    // Bee Flight tracking.
    Route::livewire('orders', 'pages::orders.index')->name('orders.index');
    Route::livewire('orders/{order}', 'pages::orders.show')->name('orders.show');

    // Honey Rewards.
    Route::livewire('rewards', 'pages::rewards')->name('rewards');

    // Bee Chat.
    Route::livewire('chat', 'pages::chat.index')->name('chat.index');
    Route::livewire('chat/{conversation}', 'pages::chat.show')->name('chat.show');
});

// Seller onboarding is open to any signed-in seller, including one whose
// business has not been approved yet.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('seller/onboarding', 'pages::seller.onboarding')->name('seller.onboarding');
    Route::livewire('seller/pending', 'pages::seller.pending')->name('seller.pending');
});

Route::middleware(['auth', 'verified', 'seller'])->group(function () {
    Route::livewire('seller/products', 'pages::seller.products')->name('seller.products');
    Route::livewire('seller/orders', 'pages::seller.orders')->name('seller.orders');
});

// Hive Guard: admin-only business and review moderation.
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::livewire('admin/businesses', 'pages::admin.businesses')->name('admin.businesses');
    Route::livewire('admin/orders', 'pages::admin.orders')->name('admin.orders');
    Route::livewire('admin/reviews', 'pages::admin.reviews')->name('admin.reviews');
});

require __DIR__.'/settings.php';
