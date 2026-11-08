<?php

it('redirects the home page to the marketplace', function () {
    $this->get(route('home'))->assertRedirect(route('marketplace'));
});
