<?php

use App\Enums\PointsReason;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('records an award as a positive ledger entry regardless of input sign', function () {
    $user = User::factory()->create();

    app(PointsService::class)->award($user, -50, PointsReason::Purchase);

    expect($user->pointsBalance())->toBe(50);
});

it('redeems points and reduces the balance', function () {
    $user = User::factory()->create();
    $service = app(PointsService::class);
    $service->award($user, 100, PointsReason::Purchase);

    $service->redeem($user, 40);

    expect($user->pointsBalance())->toBe(60)
        ->and($user->pointsLedger()->where('reason', PointsReason::Redemption->value)->value('delta'))->toBe(-40);
});

it('refuses to redeem more points than the balance', function () {
    $user = User::factory()->create();
    $service = app(PointsService::class);
    $service->award($user, 30, PointsReason::Purchase);

    expect(fn () => $service->redeem($user, 31))->toThrow(ModelNotFoundException::class)
        ->and($user->pointsBalance())->toBe(30);
});

it('rejects a non-positive redemption amount', function () {
    $user = User::factory()->create();

    expect(fn () => app(PointsService::class)->redeem($user, 0))
        ->toThrow(InvalidArgumentException::class);
});

it('reverses awarded points with a negative entry', function () {
    $user = User::factory()->create();
    $service = app(PointsService::class);
    $service->award($user, 80, PointsReason::Purchase);

    $service->reverse($user, 80, PointsReason::Refund);

    expect($user->pointsBalance())->toBe(0);
});
