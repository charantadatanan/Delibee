<?php

namespace App\Console\Commands;

use App\Services\RankingService;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publishes the BeeRank for a period. The scheduler runs this monthly for
 * the BeeRank and yearly for the Annual Business Rating.
 */
class ComputeBeeRank extends Command
{
    protected $signature = 'bee:rank
                            {--period=month : The period type: month or year}
                            {--month= : The month to publish, as YYYY-MM (defaults to the previous month)}';

    protected $description = 'Compute and store the BeeRank snapshots for a period';

    public function handle(RankingService $ranking): int
    {
        $period = $this->option('period');
        $period = is_string($period) ? $period : 'month';

        if (! in_array($period, ['month', 'year'], true)) {
            $this->error('The --period option must be "month" or "year".');

            return self::FAILURE;
        }

        if ($period === 'month') {
            [$start, $end] = $this->monthWindow();
        } else {
            [$start, $end] = $this->yearWindow();
        }

        $snapshots = $ranking->publish($period, $start, $start, $end);

        $this->info(sprintf(
            'Published %d %s snapshots for %s to %s.',
            $snapshots->count(),
            $period,
            $start->toDateString(),
            $end->toDateString(),
        ));

        foreach ($snapshots->take(10) as $snapshot) {
            $this->line(sprintf(
                '  #%d %s — %.2f',
                $snapshot->rank,
                $snapshot->business->name,
                (float) $snapshot->score,
            ));
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function monthWindow(): array
    {
        $month = $this->option('month');

        if (is_string($month) && $month !== '') {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } else {
            $start = now()->subMonth()->startOfMonth();
        }

        return [$start, $start->copy()->endOfMonth()];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function yearWindow(): array
    {
        $start = now()->subYear()->startOfYear();

        return [$start, $start->copy()->endOfYear()];
    }
}
