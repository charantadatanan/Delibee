<?php

namespace App\Support;

/**
 * The outcome of evaluating one business for a ranking period.
 */
final readonly class BeeRankResult
{
    /**
     * @param  array<string, float>  $components  Normalised 0..1 component values.
     * @param  array<string, mixed>  $metrics  Raw inputs, kept so a ranking can be explained later.
     */
    public function __construct(
        public float $score,
        public array $components,
        public array $metrics,
        public int $orderCount,
    ) {}

    public function component(string $key): ?float
    {
        return $this->components[$key] ?? null;
    }
}
