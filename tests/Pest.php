<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses()->in('Unit');

/**
 * Fortify features are toggled in config/fortify.php, so a test that depends on
 * one has to be skipped rather than failed when the feature is off.
 */
function skipUnlessFortifyHas(string $feature, ?string $message = null): void
{
    if (! Features::enabled($feature)) {
        test()->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
    }
}
