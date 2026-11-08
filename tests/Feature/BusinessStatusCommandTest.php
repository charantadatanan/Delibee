<?php

use App\Enums\BusinessStatus;
use App\Models\Business;

it('activates a pending business from the command line', function () {
    $business = Business::factory()->pending()->create();

    $this->artisan('business:status', [
        'business' => $business->slug,
        'status' => 'active',
    ])->assertSuccessful();

    expect($business->fresh()->status)->toBe(BusinessStatus::Active);
});

it('suspends an active business from the command line', function () {
    $business = Business::factory()->create();

    $this->artisan('business:status', [
        'business' => $business->slug,
        'status' => 'suspended',
    ])->assertSuccessful();

    expect($business->fresh()->status)->toBe(BusinessStatus::Suspended);
});

it('rejects an unknown status without changing the business', function () {
    $business = Business::factory()->pending()->create();

    $this->artisan('business:status', [
        'business' => $business->slug,
        'status' => 'whatever',
    ])->assertFailed();

    expect($business->fresh()->status)->toBe(BusinessStatus::Pending);
});

it('rejects an unknown business slug', function () {
    $this->artisan('business:status', [
        'business' => 'does-not-exist',
        'status' => 'active',
    ])->assertFailed();
});
