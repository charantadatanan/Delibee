<?php

use Laravel\Fortify\Features;

it('can render the registration screen', function () {
    skipUnlessFortifyHas(Features::registration());

    $this->get(route('register'))->assertOk();
});

it('allows new users to register', function () {
    skipUnlessFortifyHas(Features::registration());

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
