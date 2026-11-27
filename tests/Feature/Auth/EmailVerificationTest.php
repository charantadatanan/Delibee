<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    skipUnlessFortifyHas(Features::emailVerification());
});

it('can render the email verification screen', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('verification.notice'))->assertOk();
});

it('redirects unverified users to the email verification prompt', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('appearance.edit'))
        ->assertRedirect(route('verification.notice'));
});

it('can verify an email address', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

it('can verify an email address with a six-digit code', function () {
    $user = User::factory()->unverified()->create([
        'email_verification_code_hash' => Hash::make('123456'),
        'email_verification_code_expires_at' => now()->addMinutes(10),
    ]);

    Event::fake();

    $response = $this->actingAs($user)->post(route('verification.code'), [
        'code' => '123456',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('does not verify an email address with an expired code', function () {
    $user = User::factory()->unverified()->create([
        'email_verification_code_hash' => Hash::make('123456'),
        'email_verification_code_expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)->post(route('verification.code'), [
        'code' => '123456',
    ])->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('does not verify an email address with an invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')],
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('does not fire the verified event again for an already verified user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertNotDispatched(Verified::class);
});
