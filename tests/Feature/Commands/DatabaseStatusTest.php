<?php

it('reports database and backup health', function () {
    $this->artisan('delibee:status')
        ->expectsOutputToContain('Database')
        ->expectsOutputToContain('Migrations')
        ->expectsOutputToContain('Latest backup')
        ->assertSuccessful();
});
