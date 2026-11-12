<?php

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('shows the admin businesses page to admins', function () {
    Business::factory()->pending()->create(['name' => 'Pending Shop']);

    $this->actingAs($this->admin)
        ->get(route('admin.businesses'))
        ->assertOk()
        ->assertSee('Pending Shop');
});

it('forbids non-admins from the admin businesses page', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.businesses'))
        ->assertForbidden();
});

it('lets an admin approve a pending business', function () {
    $business = Business::factory()->pending()->create();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.businesses')
        ->call('approve', $business->id)
        ->assertHasNoErrors();

    expect($business->fresh()->status)->toBe(BusinessStatus::Active);
});

it('lets an admin suspend an active business', function () {
    $business = Business::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.businesses')
        ->call('suspend', $business->id)
        ->assertHasNoErrors();

    expect($business->fresh()->status)->toBe(BusinessStatus::Suspended);
});

it('lets an admin reactivate a suspended business', function () {
    $business = Business::factory()->suspended()->create();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.businesses')
        ->call('reactivate', $business->id)
        ->assertHasNoErrors();

    expect($business->fresh()->status)->toBe(BusinessStatus::Active);
});

it('does not approve a business that is not pending', function () {
    $business = Business::factory()->suspended()->create();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.businesses')
        ->call('approve', $business->id);

    expect($business->fresh()->status)->toBe(BusinessStatus::Suspended);
});
