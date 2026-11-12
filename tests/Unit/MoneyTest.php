<?php

use App\Support\Money;

it('converts between major and minor units', function () {
    expect(Money::toMinor(149.50))->toBe(14950)
        ->and(Money::toMinor('149.50'))->toBe(14950)
        ->and(Money::toMajor(14950))->toBe(149.5)
        ->and(Money::toMinor(1000, 'JPY'))->toBe(1000)
        ->and(Money::toMajor(1000, 'JPY'))->toBe(1000.0);
});

it('formats amounts with the ISO currency code', function () {
    expect(Money::format(18500))->toContain('PHP')
        ->and(Money::format(18500, 'USD'))->toContain('USD')
        ->and(Money::format(1000, 'JPY'))->toContain('JPY');
});

it('falls back to two decimals for unknown currencies', function () {
    expect(Money::exponent('XYZ'))->toBe(2);
});
