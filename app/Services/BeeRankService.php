<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Review;
use App\Support\BeeRankResult;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * BeeRank scores a business on **current performance**, not lifetime totals.
 *
 * A new business with two months of strong ratings and completed orders can
 * therefore outrank an established seller that has gone quiet — that is the
 * whole point of the product.
 *
 * The score combines four components over a rolling window:
 *
 *   rating         Bayesian-smoothed average of verified-purchase reviews
 *   completion     share of orders delivered rather than cancelled
 *   on_time        share of shipped orders delivered within the promised window
 *   response_time  how quickly the seller answers in Bee Chat
 *
 * Two rules keep it from being gamed:
 *
 *   1. A business needs a minimum number of orders inside the window before
 *      it can be ranked at all.
 *   2. Only verified-purchase reviews count, and a handful of five-star
 *      reviews cannot outrank many solid ones because the average is
 *      smoothed towards the marketplace-wide mean.
 */
class BeeRankService
{
    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public function window(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $to ??= now();
        $from ??= $to->copy()->subDays((int) config('beerank.window_days'));

        return [$from, $to];
    }

    /**
     * Evaluate a business. Returns null when it is not eligible to be
     * ranked, which is how the minimum order threshold is enforced.
     */
    public function evaluate(Business $business, ?CarbonInterface $from = null, ?CarbonInterface $to = null): ?BeeRankResult
    {
        [$from, $to] = $this->window($from, $to);

        $orders = Order::query()
            ->where('business_id', $business->id)
            ->whereBetween('ordered_at', [$from, $to])
            ->get();

        if ($orders->count() < (int) config('beerank.minimum_orders')) {
            return null;
        }

        $components = [];

        $completion = $this->completionComponent($orders);
        $components['completion'] = $completion;

        $onTime = $this->onTimeComponent($orders);
        if ($onTime !== null) {
            $components['on_time'] = $onTime;
        }

        $rating = $this->ratingComponent($business, $from, $to);
        if ($rating !== null) {
            $components['rating'] = $rating;
        }

        $responseTime = $this->responseTimeComponent($business, $from, $to);
        if ($responseTime !== null) {
            $components['response_time'] = $responseTime;
        }

        $weights = config('beerank.weights');

        // Components with no data are dropped and the remaining weights are
        // renormalised, so a missing metric never silently zeroes the score.
        $availableWeight = 0.0;
        foreach ($components as $key => $value) {
            $availableWeight += (float) $weights[$key];
        }

        $score = 0.0;
        if ($availableWeight > 0) {
            foreach ($components as $key => $value) {
                $score += ((float) $weights[$key] / $availableWeight) * $value;
            }
        }

        $score *= (int) config('beerank.score_scale');

        return new BeeRankResult(
            score: round($score, 4),
            components: array_map(fn (float $v): float => round($v, 4), $components),
            metrics: $this->metrics($business, $orders, $from, $to),
            orderCount: $orders->count(),
        );
    }

    /**
     * Share of orders that reached the customer rather than being cancelled.
     */
    /** @param Collection<int, Order> $orders */
    private function completionComponent(Collection $orders): float
    {
        $total = $orders->count();

        if ($total === 0) {
            return 0.0;
        }

        $completed = $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Delivered)->count();

        return $completed / $total;
    }

    /**
     * Share of shipped orders that were delivered inside the promised
     * window. Null when nothing was shipped, so the component is skipped
     * rather than counted as zero.
     */
    /** @param Collection<int, Order> $orders */
    private function onTimeComponent(Collection $orders): ?float
    {
        $shipped = $orders->filter(fn (Order $order): bool => $order->shipped_at !== null);

        if ($shipped->isEmpty()) {
            return null;
        }

        $onTime = $shipped->filter(fn (Order $order): bool => $order->isOnTime())->count();

        return $onTime / $shipped->count();
    }

    /**
     * Bayesian-smoothed average of verified-purchase reviews.
     */
    private function ratingComponent(Business $business, CarbonInterface $from, CarbonInterface $to): ?float
    {
        $smoothed = $this->smoothedRating($business, $from, $to);

        if ($smoothed === null) {
            return null;
        }

        $maxRating = (float) config('beerank.smoothing.max_rating');

        return min(1.0, max(0.0, $smoothed / $maxRating));
    }

    /**
     * The smoothed rating itself, on the same 1..5 scale as the raw
     * reviews, so it can be shown to sellers next to their real average.
     */
    private function smoothedRating(Business $business, CarbonInterface $from, CarbonInterface $to): ?float
    {
        $reviews = $this->verifiedReviews($business, $from, $to);

        if ($reviews->isEmpty()) {
            return null;
        }

        $priorWeight = (float) config('beerank.smoothing.prior_weight');
        $average = (float) $reviews->avg('rating');
        $priorMean = $this->priorMean($from, $to);

        return ($priorWeight * $priorMean + $reviews->count() * $average) / ($priorWeight + $reviews->count());
    }

    /**
     * Only published reviews tied to a delivered order item count.
     */
    /**
     * @return Collection<int, Review>
     */
    private function verifiedReviews(Business $business, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Review::query()
            ->where('business_id', $business->id)
            ->where('status', ReviewStatus::Published)
            ->whereNotNull('order_item_id')
            ->whereHas('order', fn ($query) => $query->whereBetween('ordered_at', [$from, $to]))
            ->get();
    }

    /**
     * The marketplace-wide mean of verified reviews, used as the prior. With
     * no marketplace history at all, the configured prior mean is used.
     */
    private function priorMean(CarbonInterface $from, CarbonInterface $to): float
    {
        $globalAverage = Review::query()
            ->where('status', ReviewStatus::Published)
            ->whereNotNull('order_item_id')
            ->whereHas('order', fn ($query) => $query->whereBetween('ordered_at', [$from, $to]))
            ->avg('rating');

        return $globalAverage !== null
            ? (float) $globalAverage
            : (float) config('beerank.smoothing.prior_mean');
    }

    /**
     * How quickly the seller answers in Bee Chat. Conversations the seller
     * never answered count as the slowest possible response, otherwise a
     * seller could game the metric by staying silent.
     */
    private function responseTimeComponent(Business $business, CarbonInterface $from, CarbonInterface $to): ?float
    {
        $conversations = Conversation::query()
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        $fast = (float) config('beerank.response_time_hours.fast');
        $slow = (float) config('beerank.response_time_hours.slow');

        $hours = $conversations->map(function (Conversation $conversation) use ($slow): float {
            $response = $conversation->responseTimeHours();

            return $response ?? $slow;
        });

        $average = (float) $hours->avg();

        if ($average <= $fast) {
            return 1.0;
        }

        if ($average >= $slow) {
            return 0.0;
        }

        return round(1 - (($average - $fast) / ($slow - $fast)), 4);
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array<string, mixed>
     */
    private function metrics(Business $business, Collection $orders, CarbonInterface $from, CarbonInterface $to): array
    {
        $reviews = $this->verifiedReviews($business, $from, $to);

        $shipped = $orders->filter(fn (Order $order): bool => $order->shipped_at !== null);

        return [
            'orders' => $orders->count(),
            'completed' => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Delivered)->count(),
            'cancelled' => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Cancelled)->count(),
            'shipped' => $shipped->count(),
            'on_time' => $shipped->filter(fn (Order $order): bool => $order->isOnTime())->count(),
            'reviews' => $reviews->count(),
            'rating_average' => $reviews->isEmpty() ? null : round((float) $reviews->avg('rating'), 4),
            'rating_smoothed' => $this->smoothedRating($business, $from, $to),
            'period_start' => $from->toDateString(),
            'period_end' => $to->toDateString(),
        ];
    }
}
