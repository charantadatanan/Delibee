<?php

use App\Enums\OrderStatus;
use App\Enums\PointsReason;
use App\Enums\ReviewStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointsLedgerEntry;
use App\Models\Review;
use App\Services\CheckoutService;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Order Details')] class extends Component
{
    public Order $order;

    /**
     * The rating the customer is leaving for a given order item.
     *
     * @var array<int, int>
     */
    public array $ratings = [];

    /**
     * @var array<int, string>
     */
    public array $reviewBodies = [];

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);

        $this->order = $order->load(['business', 'items.product', 'statusEvents.actor']);
    }

    #[Computed]
    public function timeline(): array
    {
        $steps = collect(OrderStatus::progression())->map(fn (OrderStatus $status): array => [
            'status' => $status,
            'at' => $this->order->statusEvents->firstWhere('status', $status)?->created_at,
        ]);

        return $steps->all();
    }

    public function leaveReview(int $orderItemId): void
    {
        $item = $this->order->items->firstWhere('id', $orderItemId);

        if ($item === null || ! $this->order->status->isTerminal()) {
            return;
        }

        $this->authorize('review', $this->order);

        $validated = $this->validate([
            "ratings.{$orderItemId}" => ['required', 'integer', 'min:1', 'max:5'],
            "reviewBodies.{$orderItemId}" => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($item, $orderItemId, $validated): void {
            $review = Review::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'order_item_id' => $orderItemId,
                ],
                [
                    'business_id' => $this->order->business_id,
                    'product_id' => $item->product_id,
                    'order_id' => $this->order->id,
                    'rating' => $validated['ratings'][$orderItemId],
                    'title' => null,
                    'body' => $validated['reviewBodies'][$orderItemId] ?? null,
                    'status' => ReviewStatus::Published,
                ],
            );

            // Reward the first submission only; editing a rating must not
            // farm the review bonus again.
            if ($review->wasRecentlyCreated) {
                app(PointsService::class)->award(
                    auth()->user(),
                    (int) config('commerce.review_reward_points'),
                    PointsReason::ReviewReward,
                    OrderItem::class,
                    $orderItemId,
                );
            }
        });

        \Flux\Flux::toast('Thanks for your rating.', variant: 'success');

        unset($this->timeline);
    }

    public function cancel(): void
    {
        $this->authorize('cancel', $this->order);

        if (! $this->order->status->isCancellable()) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $this->order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $this->order->statusEvents()->create([
                'status' => OrderStatus::Cancelled,
                'note' => 'Order cancelled.',
                'created_by' => auth()->id(),
            ]);

            $this->reversePurchasePoints();
        });

        unset($this->timeline);
    }

    /**
     * Put back the points the order earned, once, when it is cancelled.
     */
    private function reversePurchasePoints(): void
    {
        $ledger = PointsLedgerEntry::query()
            ->where('user_id', $this->order->user_id)
            ->where('reference_type', Order::class)
            ->where('reference_id', $this->order->id);

        $wasAwarded = (clone $ledger)->where('reason', PointsReason::Purchase)->exists();
        $alreadyReversed = (clone $ledger)->where('reason', PointsReason::Refund)->exists();

        if (! $wasAwarded || $alreadyReversed) {
            return;
        }

        app(PointsService::class)->reverse(
            $this->order->user,
            app(CheckoutService::class)->pointsFor($this->order),
            PointsReason::Refund,
            Order::class,
            $this->order->id,
        );
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Order {{ $order->number }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $order->business->name }}, placed {{ $order->ordered_at?->format('j M Y, g:i a') }}
            </p>
        </div>

        @php($statusClasses = match ($order->status) {
            OrderStatus::Delivered => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
            OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
            OrderStatus::Shipped, OrderStatus::OutForDelivery => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-300',
            default => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
        })

        <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusClasses }}">
            {{ $order->status->label() }}
        </span>
    </div>

    <section class="rounded-2xl border border-comb/90 bg-white/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70">
        <h2 class="font-semibold">Tracking</h2>

        <ol class="mt-4 space-y-4">
            @foreach ($this->timeline() as $step)
                <li class="flex gap-3">
                    <span class="mt-1 grid size-4 shrink-0 place-items-center rounded-full
                        {{ $step['at'] ? 'bg-honey' : 'bg-comb dark:bg-zinc-700' }}"></span>
                    <div>
                        <p class="text-sm font-medium {{ $step['at'] ? '' : 'text-zinc-400' }}">
                            {{ $step['status']->label() }}
                        </p>
                        @if ($step['at'])
                            <p class="text-xs text-zinc-500">{{ $step['at']->format('j M Y, g:i a') }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        @if ($order->status->isCancellable())
            <button type="button" wire:click="cancel" wire:confirm="Cancel this order?"
                    class="mt-5 rounded-lg border border-red-300 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-500/10">
                Cancel order
            </button>
        @endif
    </section>

    <section class="rounded-2xl border border-comb/90 bg-white/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70">
        <h2 class="font-semibold">Items</h2>

        <ul class="mt-4 divide-y divide-comb/70 dark:divide-zinc-800">
            @foreach ($order->items as $item)
                <li class="py-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-xs text-zinc-500">
                                {{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price_minor, $order->currency) }}
                            </p>
                        </div>
                        <p class="font-semibold">{{ \App\Support\Money::format($item->subtotal_minor, $order->currency) }}</p>
                    </div>

                    @if ($order->status === OrderStatus::Delivered)
                        <div class="mt-3 rounded-xl bg-comb/30 p-3 dark:bg-zinc-950/60">
                            @php($existing = $item->reviews->first())

                            @if ($existing)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Your rating: <span class="text-honey">{{ str_repeat('★', $existing->rating) }}</span>
                                </p>
                            @else
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Rate this item</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <select wire:model="ratings.{{ $item->id }}"
                                            class="rounded-lg border border-comb bg-paper/60 px-3 py-1.5 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                                        <option value="">Choose a rating</option>
                                        @for ($star = 5; $star >= 1; $star--)
                                            <option value="{{ $star }}">{{ $star }} star{{ $star === 1 ? '' : 's' }}</option>
                                        @endfor
                                    </select>

                                    <input type="text"
                                           wire:model="reviewBodies.{{ $item->id }}"
                                           placeholder="Tell other buyers about it (optional)"
                                           class="flex-1 rounded-lg border border-comb bg-paper/60 px-3 py-1.5 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">

                                    <button type="button"
                                            wire:click="leaveReview({{ $item->id }})"
                                            class="rounded-lg bg-ink px-3 py-1.5 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                                        Submit
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <dl class="mt-4 space-y-1 border-t border-comb/70 pt-4 text-sm dark:border-zinc-800">
            <div class="flex justify-between">
                <dt class="text-zinc-500 dark:text-zinc-400">Subtotal</dt>
                <dd>{{ \App\Support\Money::format($order->subtotal_minor, $order->currency) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-zinc-500 dark:text-zinc-400">Shipping</dt>
                <dd>{{ \App\Support\Money::format($order->shipping_minor, $order->currency) }}</dd>
            </div>
            <div class="flex justify-between pt-1 text-base font-semibold">
                <dt>Total</dt>
                <dd>{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</dd>
            </div>
        </dl>

        @if ($order->shipping_address)
            <div class="mt-4 border-t border-comb/70 pt-4 text-sm dark:border-zinc-800">
                <p class="font-medium">Delivering to</p>
                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ $order->shipping_address['name'] ?? '' }}<br>
                    {{ $order->shipping_address['line1'] ?? '' }}<br>
                    {{ $order->shipping_address['city'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                    {{ $order->shipping_address['phone'] ?? '' }}
                </p>
            </div>
        @endif
    </section>
</div>
