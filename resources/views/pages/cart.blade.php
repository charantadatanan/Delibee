<?php

use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Honey Pot')] class extends Component
{
    #[Computed]
    public function groups()
    {
        return app(CartService::class)->groups(auth()->user());
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function subtotals(): array
    {
        return app(CartService::class)->subtotalsByCurrency(auth()->user());
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->updateQuantity(auth()->user(), $item, $quantity);

        unset($this->groups, $this->subtotals);
    }

    public function increment(int $itemId): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->updateQuantity(auth()->user(), $item, $item->quantity + 1);

        unset($this->groups, $this->subtotals);
    }

    public function decrement(int $itemId): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->updateQuantity(auth()->user(), $item, max(1, $item->quantity - 1));

        unset($this->groups, $this->subtotals);
    }

    public function remove(int $itemId): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->remove(auth()->user(), $item);

        unset($this->groups, $this->subtotals);
    }
};
?>

@php
    $totalItems = $this->groups()->flatten()->sum(fn ($item) => $item->quantity);
    $subtotals = $this->subtotals();
    $primaryCurrency = array_key_first($subtotals);
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="'Cart ('.$totalItems.')'" :back="route('marketplace')" />

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Honey Pot</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Your basket, many sellers</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Checkout splits this into one order per seller, each shipped separately.
        </p>
    </div>

    @if ($this->groups()->isEmpty())
        <x-ui.empty-state
            class="mx-auto mt-4 max-w-xl"
            title="Your Honey Pot is empty"
            description="Find something good from a local seller and it will land here."
            icon="shopping-bag">
            <a href="{{ route('marketplace') }}" class="ui-btn" wire:navigate>Browse the marketplace</a>
        </x-ui.empty-state>
    @else
        <div class="grid gap-5 lg:grid-cols-[1fr_340px] lg:items-start">
            <div class="space-y-4">
                @foreach ($this->groups() as $businessId => $items)
                    @php($business = $items->first()->product->business)

                    <section class="ui-card overflow-hidden bg-paper dark:bg-zinc-900" aria-label="Items from {{ $business->name }}">
                        <header class="flex items-center gap-3 border-b border-ink/15 p-3 dark:border-zinc-800">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full border border-ink/50 bg-honey font-display text-xs font-bold text-ink">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                            </span>

                            <a href="{{ route('businesses.show', $business->slug) }}"
                               class="min-w-0 flex-1 truncate text-sm font-semibold transition hover:text-honey-deep dark:hover:text-honey"
                               wire:navigate>
                                {{ $business->name }}
                            </a>

                            <a href="{{ route('businesses.show', $business->slug) }}"
                               class="text-xs font-medium text-honey-deep underline-offset-4 hover:underline dark:text-honey"
                               wire:navigate>
                                View shop
                            </a>
                        </header>

                        <ul class="divide-y divide-ink/10 dark:divide-zinc-800">
                            @foreach ($items as $item)
                                <li class="flex gap-3 p-3">
                                    <a href="{{ route('products.show', [$business->slug, $item->product->slug]) }}"
                                       class="relative grid size-[72px] shrink-0 place-items-center overflow-hidden rounded-xl border border-ink/40 bg-hero dark:border-zinc-700 dark:bg-zinc-950"
                                       wire:navigate>
                                        @if ($item->product->images->isNotEmpty())
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($item->product->images->first()->path) }}"
                                                 alt=""
                                                 loading="lazy"
                                                 class="size-full object-cover">
                                        @else
                                            <span class="font-display text-xl font-semibold text-honey-deep dark:text-honey">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->product->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </a>

                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('products.show', [$business->slug, $item->product->slug]) }}"
                                           class="line-clamp-2 text-sm font-semibold leading-tight transition hover:text-honey-deep dark:hover:text-honey"
                                           wire:navigate>
                                            {{ $item->product->name }}
                                        </a>

                                        <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                            {{ $item->product->formattedPrice() }} each · {{ $item->product->stock }} available
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <div class="inline-flex items-center rounded-full border border-ink/50 bg-white dark:border-zinc-700 dark:bg-zinc-950" role="group" aria-label="Quantity for {{ $item->product->name }}">
                                                <button type="button"
                                                        wire:click="decrement({{ $item->id }})"
                                                        @disabled($item->quantity <= 1)
                                                        class="grid size-8 place-items-center rounded-full text-base font-semibold transition hover:bg-hero disabled:opacity-40"
                                                        aria-label="Decrease quantity">−</button>
                                                <span class="min-w-7 text-center text-xs font-semibold" aria-live="polite">{{ $item->quantity }}</span>
                                                <button type="button"
                                                        wire:click="increment({{ $item->id }})"
                                                        @disabled($item->quantity >= $item->product->stock)
                                                        class="grid size-8 place-items-center rounded-full text-base font-semibold transition hover:bg-hero disabled:opacity-40"
                                                        aria-label="Increase quantity">+</button>
                                            </div>

                                            <button type="button"
                                                    wire:click="remove({{ $item->id }})"
                                                    wire:confirm="Remove {{ $item->product->name }} from your Honey Pot?"
                                                    class="text-[11px] font-medium text-zinc-500 underline-offset-4 hover:text-red-700 hover:underline dark:text-zinc-400">
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                    <p class="ui-price shrink-0 text-sm">
                                        {{ \App\Support\Money::format($item->subtotalMinor(), $item->product->currency) }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach

                <div class="ui-note flex items-start gap-2">
                    <flux:icon name="information-circle" class="mt-0.5 size-4 shrink-0" />
                    <p>Each seller is paid and ships separately. Shipping is added per seller at checkout.</p>
                </div>
            </div>

            <aside class="ui-card bg-paper p-4 dark:bg-zinc-900 lg:sticky lg:top-24">
                <h2 class="font-display text-xl font-semibold">Order summary</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    @foreach ($subtotals as $currency => $amount)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-600 dark:text-zinc-400">Subtotal ({{ $currency }})</dt>
                            <dd class="font-semibold">{{ \App\Support\Money::format($amount, $currency) }}</dd>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-3 border-t border-ink/15 pt-3 dark:border-zinc-800">
                        <dt class="text-zinc-600 dark:text-zinc-400">Shipping</dt>
                        <dd class="text-xs text-zinc-500 dark:text-zinc-400">Added per seller</dd>
                    </div>
                </dl>

                <a href="{{ route('checkout') }}" class="ui-btn mt-5 w-full" wire:navigate>
                    Checkout
                    <flux:icon name="arrow-right" class="size-4" />
                </a>

                <a href="{{ route('marketplace') }}" class="mt-3 block text-center text-xs font-medium text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                    Continue shopping
                </a>

                <p class="mt-4 text-center text-[10px] text-zinc-500 dark:text-zinc-400">
                    {{ $totalItems }} {{ \Illuminate\Support\Str::plural('item', $totalItems) }} · {{ $primaryCurrency ? 'Totals shown in '.$primaryCurrency : '' }}
                </p>
            </aside>
        </div>
    @endif
</div>
