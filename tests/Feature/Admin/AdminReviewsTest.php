<?php

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('shows the admin reviews page to admins', function () {
    Review::factory()->create(['body' => 'A review that needs a look.']);

    $this->actingAs($this->admin)
        ->get(route('admin.reviews'))
        ->assertOk()
        ->assertSee('A review that needs a look.');
});

it('forbids non-admins from the admin reviews page', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.reviews'))
        ->assertForbidden();
});

it('lets an admin hide a published review', function () {
    $review = Review::factory()->create(['status' => ReviewStatus::Published]);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.reviews')
        ->call('hide', $review->id)
        ->assertHasNoErrors();

    expect($review->fresh()->status)->toBe(ReviewStatus::Hidden);
});

it('lets an admin publish a hidden review', function () {
    $review = Review::factory()->create(['status' => ReviewStatus::Hidden]);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.reviews')
        ->call('publish', $review->id)
        ->assertHasNoErrors();

    expect($review->fresh()->status)->toBe(ReviewStatus::Published);
});

it('does not let non-admins moderate reviews', function () {
    $review = Review::factory()->create(['status' => ReviewStatus::Published]);
    $customer = User::factory()->create();

    expect(Gate::forUser($customer)->allows('moderate', $review))->toBeFalse();
});

it('removes a hidden review from the public storefront', function () {
    $review = Review::factory()->create([
        'body' => 'This review should disappear.',
        'status' => ReviewStatus::Published,
    ]);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.reviews')
        ->call('hide', $review->id)
        ->assertHasNoErrors();

    $this->get(route('businesses.show', $review->business->slug))
        ->assertOk()
        ->assertDontSee('This review should disappear.');
});
