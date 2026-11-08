<?php

use App\Models\User;

it('can render the password confirmation screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('password.confirm'))->assertOk();
});
