<?php

namespace App\Support;

use NumberFormatter;

/**
 * Amounts are stored as integers in minor units (centavos) alongside a
 * currency code. This helper is the only place that converts between the
 * stored integer and a human-readable string, so rounding never happens
 * implicitly somewhere else.
 */
final class Money
{
    /** @var array<string, int> */
    private const EXPONENT = [
        'PHP' => 2,
        'USD' => 2,
        'EUR' => 2,
        'JPY' => 0,
        'KRW' => 0,
    ];

    public static function exponent(string $currency): int
    {
        return self::EXPONENT[strtoupper($currency)] ?? 2;
    }

    /**
     * Convert a decimal amount (e.g. 149.50) into minor units.
     */
    public static function toMinor(float|int|string $amount, string $currency = 'PHP'): int
    {
        $exponent = self::exponent($currency);

        return (int) round((float) $amount * (10 ** $exponent));
    }

    /**
     * Convert minor units into a decimal amount.
     */
    public static function toMajor(int $minor, string $currency = 'PHP'): float
    {
        return $minor / (10 ** self::exponent($currency));
    }

    public static function format(int $minor, string $currency = 'PHP'): string
    {
        $formatter = new NumberFormatter('en_PH', NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, strtoupper($currency));

        $formatted = $formatter->formatCurrency(self::toMajor($minor, $currency), strtoupper($currency));

        // The CURRENCY style follows the locale's symbol placement; keeping the
        // ISO code makes multi-currency carts unambiguous.
        return $formatted !== false ? $formatted.' '.strtoupper($currency) : number_format(self::toMajor($minor, $currency), 2).' '.strtoupper($currency);
    }
}
