<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('prunes abandoned sessions and old failed jobs', function () {
    $now = now();

    DB::table('sessions')->insert([
        ['id' => 'stale-session', 'user_id' => null, 'ip_address' => null, 'user_agent' => null, 'payload' => '', 'last_activity' => $now->copy()->subDays(10)->getTimestamp()],
        ['id' => 'fresh-session', 'user_id' => null, 'ip_address' => null, 'user_agent' => null, 'payload' => '', 'last_activity' => $now->copy()->subMinute()->getTimestamp()],
    ]);

    DB::table('failed_jobs')->insert([
        ['uuid' => (string) Str::uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Exception', 'failed_at' => $now->copy()->subDays(40)],
        ['uuid' => (string) Str::uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Exception', 'failed_at' => $now->copy()->subDay()],
    ]);

    $this->artisan('delibee:prune')
        ->expectsOutputToContain('Pruned 1 stale session(s) and 1 failed job(s).')
        ->assertSuccessful();

    expect(DB::table('sessions')->pluck('id')->all())->toBe(['fresh-session'])
        ->and(DB::table('failed_jobs')->count())->toBe(1);
});

it('honours custom retention windows', function () {
    DB::table('sessions')->insert([
        'id' => 'ten-day-session',
        'user_id' => null,
        'ip_address' => null,
        'user_agent' => null,
        'payload' => '',
        'last_activity' => now()->subDays(10)->getTimestamp(),
    ]);

    $this->artisan('delibee:prune', ['--session-days' => 30])
        ->expectsOutputToContain('Pruned 0 stale session(s)')
        ->assertSuccessful();

    expect(DB::table('sessions')->where('id', 'ten-day-session')->exists())->toBeTrue();
});

it('schedules the prune command daily', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('delibee:prune')
        ->assertSuccessful();
});
