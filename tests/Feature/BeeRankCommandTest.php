<?php

it('rejects an unknown period for the ranking command', function () {
    $this->artisan('bee:rank', ['--period' => 'week'])
        ->expectsOutputToContain('month')
        ->assertFailed();
});

it('rejects an invalid month for the ranking command', function () {
    $this->artisan('bee:rank', ['--period' => 'month', '--month' => 'not-a-month'])
        ->expectsOutputToContain('YYYY-MM')
        ->assertFailed();
});
