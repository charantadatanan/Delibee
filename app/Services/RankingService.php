<?php

namespace App\Services;

use App\Models\Business;
use App\Models\RankSnapshot;
use App\Support\BeeRankResult;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns BeeRank scores into published rankings.
 *
 * A snapshot records the score, the rank and the raw component metrics for a
 * period, so a published ranking can always be explained after the fact and
 * a business that has since gone quiet keeps the history it earned.
 */
class RankingService
{
    public function __construct(private readonly BeeRankService $beeRank) {}

    /**
     * Evaluate every eligible business for a period, highest score first.
     *
     * @return Collection<int, array{business: Business, result: BeeRankResult}>
     */
    public function rankAll(?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        [$from, $to] = $this->beeRank->window($from, $to);

        $results = new Collection;

        Business::query()->approved()->get()->each(function (Business $business) use ($from, $to, $results): void {
            $result = $this->beeRank->evaluate($business, $from, $to);

            if ($result !== null) {
                $results->push(['business' => $business, 'result' => $result]);
            }
        });

        return $results->sortByDesc(fn (array $row): float => $row['result']->score)->values();
    }

    /**
     * Compute and store the ranking for a period.
     *
     * @return Collection<int, RankSnapshot>
     */
    public function publish(string $periodType, CarbonInterface $periodStart, ?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        // Publishing is all-or-nothing, and a re-publish replaces the period
        // entirely: businesses that dropped below the threshold must not keep
        // a stale rank.
        return DB::transaction(function () use ($periodType, $periodStart, $from, $to): Collection {
            $ranked = $this->rankAll($from, $to);

            RankSnapshot::query()
                ->where('period_type', $periodType)
                ->where('period_start', $periodStart->toDateString())
                ->delete();

            $snapshots = new Collection;
            $rank = 0;

            foreach ($ranked as $row) {
                $rank++;

                $snapshots->push(RankSnapshot::create([
                    'business_id' => $row['business']->id,
                    'period_type' => $periodType,
                    'period_start' => $periodStart->toDateString(),
                    'score' => $row['result']->score,
                    'rank' => $rank,
                    'metrics' => array_merge($row['result']->metrics, $row['result']->components),
                ]));
            }

            return $snapshots;
        });
    }

    /**
     * The published ranking for a period, best first.
     *
     * @return Collection<int, RankSnapshot>
     */
    public function published(string $periodType, CarbonInterface $periodStart): Collection
    {
        return RankSnapshot::query()
            ->with('business')
            ->where('period_type', $periodType)
            ->where('period_start', $periodStart->toDateString())
            ->orderBy('rank')
            ->get();
    }

    /**
     * Rising Bees: young businesses that have already cleared the minimum
     * order threshold. The age limit keeps established sellers out of a
     * "new and rising" list.
     *
     * Only young businesses are evaluated: scoring the whole marketplace and
     * filtering afterwards made every ranking page load O(all businesses).
     *
     * @return Collection<int, array{business: Business, result: BeeRankResult}>
     */
    public function risingBees(?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        [$from, $to] = $this->beeRank->window($from, $to);

        $cutoff = now()->subMonths((int) config('beerank.rising_bee_months'));

        $results = new Collection;

        Business::query()
            ->approved()
            ->where('created_at', '>', $cutoff)
            ->get()
            ->each(function (Business $business) use ($from, $to, $results): void {
                $result = $this->beeRank->evaluate($business, $from, $to);

                if ($result !== null) {
                    $results->push(['business' => $business, 'result' => $result]);
                }
            });

        return $results->sortByDesc(fn (array $row): float => $row['result']->score)->values();
    }
}
