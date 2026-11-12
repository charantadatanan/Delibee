<?php

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('forbids a suspended seller from the seller desk', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    Business::factory()->for($seller, 'owner')->suspended()->create();

    $this->actingAs($seller)->get(route('seller.products'))->assertForbidden();
    $this->actingAs($seller)->get(route('seller.orders'))->assertForbidden();
});

it('redirects a pending seller to the pending page', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    Business::factory()->for($seller, 'owner')->pending()->create();

    $this->actingAs($seller)->get(route('seller.products'))->assertRedirect(route('seller.pending'));
    $this->actingAs($seller)->get(route('seller.orders'))->assertRedirect(route('seller.pending'));
});

it('redirects a seller without a business to onboarding', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $this->actingAs($seller)->get(route('seller.products'))->assertRedirect(route('seller.onboarding'));
});

it('forbids a non-seller from the seller desk', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get(route('seller.products'))->assertForbidden();
});

it('redirects a seller with a business away from onboarding', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    Business::factory()->for($seller, 'owner')->create();

    $this->actingAs($seller)->get(route('seller.onboarding'))->assertRedirect(route('seller.products'));
});
