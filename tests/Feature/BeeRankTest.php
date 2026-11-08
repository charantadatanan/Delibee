<?php

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Review;
use App\Services\BeeRankService;

it('does not rank a business below the minimum order count', function () {
    $business = Business::factory()->create();
    Order::factory()->count(4)->for($business)->delivered()->create();

    expect(app(BeeRankService::class)->evaluate($business))->toBeNull();
});

it('scores completion and on-time delivery from the rolling window', function () {
    $business = Business::factory()->create();
    Order::factory()->count(3)->for($business)->delivered()->create();
    Order::factory()->count(2)->for($business)->cancelled()->create();

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result)->not->toBeNull()
        ->and($result->component('completion'))->toBe(0.6)
        ->and($result->component('on_time'))->toBe(1.0)
        ->and($result->component('rating'))->toBeNull()
        ->and($result->component('response_time'))->toBeNull();
});

it('counts late deliveries against on-time performance', function () {
    $business = Business::factory()->create();
    Order::factory()->count(2)->for($business)->delivered()->create();
    Order::factory()->count(3)->for($business)->deliveredLate()->create();

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result->component('on_time'))->toBe(0.4)
        ->and($result->component('completion'))->toBe(1.0);
});

it('smooths the rating towards the marketplace mean', function () {
    $business = Business::factory()->create();
    $other = Business::factory()->create();

    Order::factory()->count(5)->for($business)->delivered()->create();

    Review::factory()->verified()->create(['business_id' => $business->id, 'rating' => 5]);
    Review::factory()->verified()->create(['business_id' => $other->id, 'rating' => 3]);

    $result = app(BeeRankService::class)->evaluate($business);

    // Prior mean 4, prior weight 5, one 5-star review: (5 * 4 + 1 * 5) / 6 = 4.1667, scaled by 5.
    expect($result->component('rating'))->toEqualWithDelta(0.8333, 0.001);
});

it('ignores unverified and unpublished reviews', function () {
    $business = Business::factory()->create();
    Order::factory()->count(5)->for($business)->delivered()->create();

    Review::factory()->create(['business_id' => $business->id, 'rating' => 5]);

    Review::factory()->verified()->create([
        'business_id' => $business->id,
        'rating' => 5,
        'status' => ReviewStatus::Pending,
    ]);

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result->component('rating'))->toBeNull();
});

it('scores chat response time from the first seller reply', function () {
    $business = Business::factory()->create();
    Order::factory()->count(5)->for($business)->delivered()->create();

    Conversation::factory()->for($business)->create([
        'created_at' => now()->subHours(4),
        'first_reply_at' => now()->subHours(3),
    ]);

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result->component('response_time'))->toBe(1.0);
});

it('treats an unanswered conversation as the slowest response', function () {
    $business = Business::factory()->create();
    Order::factory()->count(5)->for($business)->delivered()->create();

    Conversation::factory()->for($business)->create([
        'created_at' => now()->subHours(30),
        'first_reply_at' => null,
    ]);

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result->component('response_time'))->toBe(0.0);
});

it('renormalises the weights when components have no data', function () {
    $business = Business::factory()->create();
    Order::factory()->count(5)->for($business)->delivered()->create();

    $result = app(BeeRankService::class)->evaluate($business);

    expect($result->component('on_time'))->toBe(1.0)
        ->and($result->component('completion'))->toBe(1.0)
        ->and($result->component('rating'))->toBeNull()
        ->and($result->score)->toBe(100.0);
});
