<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The BeeRank is published for the previous month just after the month ends,
// so a full month of trading is always included.
Schedule::command('bee:rank --period=month')
    ->monthlyOn(1, '02:15')
    ->withoutOverlapping();

// The Annual Business Rating summarises consistency across the whole year.
Schedule::command('bee:rank --period=year')
    ->yearlyOn(1, 1, '03:30')
    ->withoutOverlapping();

// Housekeeping: sessions abandoned long past their 120-minute lifetime and
// failed jobs kept for debugging have no ongoing value. Rank snapshots are
// deliberately kept indefinitely as historical records.
Schedule::command('delibee:prune')
    ->dailyAt('03:05')
    ->withoutOverlapping();
