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
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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

        $limit = (int) config('commerce.review_rate_limit_per_minute', 10);
        $key = 'review-submit:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            Flux::toast('You are submitting reviews too quickly. Please wait a moment.', variant: 'danger');

            return;
        }

        RateLimiter::hit($key, 60);

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

@php
    $estimatedDelivery = $order->ordered_at?->copy()->addDays(3);
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Track Order')" :back="route('orders.index')">
        <x-slot:actions>
            <span class="rounded-full border border-ink/30 bg-white px-2 py-1 text-[9px] font-semibold dark:border-zinc-700 dark:bg-zinc-900">
                #{{ $order->number }}
            </span>
        </x-slot:actions>
    </x-ui.mobile-header>

    <div class="hidden lg:flex lg:items-start lg:justify-between lg:gap-6">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Bee Flight</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Order {{ $order->number }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ $order->business->name }} · placed {{ $order->ordered_at?->format('j M Y, g:i a') }}
            </p>
        </div>

        <x-ui.order-status :status="$order->status" class="mt-2 text-sm" />
    </div>

    <div class="grid gap-4 lg:grid-cols-[1fr_360px] lg:items-start">
        <div class="space-y-4">
            <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-xl font-semibold">Delivery Status Timeline</h2>
                        @if ($estimatedDelivery)
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                Estimated {{ $order->status === \App\Enums\OrderStatus::Delivered ? 'delivered' : 'delivery' }}: {{ $estimatedDelivery->format('F j') }}
                            </p>
                        @endif
                    </div>
                    <span class="grid size-10 place-items-center rounded-full border border-ink/50 bg-honey">
                        <flux:icon name="truck" class="size-5 text-ink" />
                    </span>
                </div>

                <ol class="mt-5 space-y-0">
                    @foreach ($this->timeline() as $step)
                        @php
                            $done = $step['at'] !== null;
                            $current = $step['status'] === $order->status;
                            $isLast = $loop->last;
                        @endphp

                        <li class="relative flex gap-3 pb-5 last:pb-0">
                            @unless ($isLast)
                                <span class="absolute start-[13px] top-7 bottom-0 w-px {{ $done ? 'bg-honey-deep/40' : 'bg-ink/15 dark:bg-zinc-700' }}" aria-hidden="true"></span>
                            @endunless

                            <span class="relative z-10 mt-0.5 grid size-7 shrink-0 place-items-center rounded-full border
                                {{ $done ? 'border-[#B8860B] bg-honey text-ink' : ($current ? 'border-ink bg-paper text-ink dark:border-zinc-300 dark:bg-zinc-950' : 'border-ink/25 bg-white text-transparent dark:border-zinc-700 dark:bg-zinc-950') }}">
                                @if ($done)
                                    <flux:icon name="check" class="size-3.5" stroke-width="3" />
                                @elseif ($current)
                                    <span class="size-2 rounded-full bg-ink dark:bg-zinc-200"></span>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold {{ $done || $current ? '' : 'text-zinc-400 dark:text-zinc-500' }}">
                                    {{ $step['status']->label() }}
                                </p>
                                @if ($step['at'])
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $step['at']->format('M j, Y · g:i a') }}</p>
                                @elseif ($current)
                                    <p class="mt-0.5 text-xs font-medium text-honey-deep dark:text-honey">In progress</p>
                                @else
                                    <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Waiting for the hive</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                    <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                        This order was cancelled. Any stock was returned to the seller.
                    </p>
                @endif
            </section>

            <section class="ui-card bg-paper dark:bg-zinc-900">
                <header class="flex items-center justify-between gap-3 border-b border-ink/15 p-4 dark:border-zinc-800">
                    <div>
                        <h2 class="font-display text-xl font-semibold">Items</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }} on this flight</p>
                    </div>
                    <x-ui.order-status :status="$order->status" />
                </header>

                <ul class="divide-y divide-ink/10 dark:divide-zinc-800">
                    @foreach ($order->items as $item)
                        <li class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $item->product_name }}</p>
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price_minor, $order->currency) }}
                                    </p>
                                </div>
                                <p class="ui-price shrink-0 text-sm">{{ \App\Support\Money::format($item->subtotal_minor, $order->currency) }}</p>
                            </div>

                            @if ($order->status === \App\Enums\OrderStatus::Delivered)
                                <div class="mt-3 rounded-xl border border-ink/20 bg-white/70 p-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                                    @php($existing = $item->reviews->first())

                                    @if ($existing)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Your rating: <span class="text-honey-deep">{{ str_repeat('★', $existing->rating) }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs font-semibold">Rate this item</p>
                                        <div class="mt-2 grid gap-2 sm:grid-cols-[140px_1fr_auto]">
                                            <select wire:model="ratings.{{ $item->id }}" aria-label="Rating for {{ $item->product_name }}" class="ui-field py-2 text-xs">
                                                <option value="">Choose a rating</option>
                                                @for ($star = 5; $star >= 1; $star--)
                                                    <option value="{{ $star }}">{{ $star }} star{{ $star === 1 ? '' : 's' }}</option>
                                                @endfor
                                            </select>

                                            <input type="text"
                                                   wire:model="reviewBodies.{{ $item->id }}"
                                                   placeholder="Tell other buyers about it (optional)"
                                                   aria-label="Review for {{ $item->product_name }}"
                                                   class="ui-field py-2 text-xs">

                                            <button type="button" wire:click="leaveReview({{ $item->id }})" class="ui-btn ui-btn-sm">
                                                Submit
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <dl class="space-y-2 border-t border-ink/15 p-4 text-sm dark:border-zinc-800">
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Subtotal</dt>
                        <dd>{{ \App\Support\Money::format($order->subtotal_minor, $order->currency) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Shipping</dt>
                        <dd>{{ \App\Support\Money::format($order->shipping_minor, $order->currency) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-ink/15 pt-2 text-base font-semibold dark:border-zinc-800">
                        <dt>Total</dt>
                        <dd class="ui-price">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            @if ($order->shipping_address)
                <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="map-pin" class="size-4 text-honey-deep dark:text-honey" />
                        <h2 class="font-display text-lg font-semibold">Delivering to</h2>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        <span class="font-medium text-ink dark:text-zinc-100">{{ $order->shipping_address['name'] ?? '' }}</span><br>
                        {{ $order->shipping_address['line1'] ?? '' }}<br>
                        {{ $order->shipping_address['city'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                        {{ $order->shipping_address['phone'] ?? '' }}
                    </p>
                </section>
            @endif

            <section class="ui-card-honey p-4">
                <div class="flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-full border border-ink/40 bg-white/70 font-display text-xs font-bold">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($order->business->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $order->business->name }}</p>
                        <p class="text-[10px] text-zinc-700">Your seller on this order</p>
                    </div>
                </div>

                <a href="{{ route('businesses.show', $order->business->slug) }}" class="ui-btn ui-btn-ghost ui-btn-sm mt-3 w-full" wire:navigate>
                    <flux:icon name="chat-bubble-left-right" class="size-4" />
                    Message seller
                </a>
            </section>

            @if ($order->status->isCancellable())
                <button type="button"
                        wire:click="cancel"
                        wire:confirm="Cancel this order?"
                        class="w-full rounded-full border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    Cancel order
                </button>
            @endif
        </aside>
    </div>
</div>
