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

    public function remove(int $itemId): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->remove(auth()->user(), $item);

        unset($this->groups, $this->subtotals);
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Honey Pot</h1>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Your cart. Checkout will split it into one order per seller.
        </p>
    </div>

    @if ($this->groups()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">Your Honey Pot is empty.</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Find something good in the marketplace.</p>
            <a href="{{ route('marketplace') }}"
               class="mt-5 inline-block rounded-lg bg-ink px-4 py-2 font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
               wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        @foreach ($this->groups() as $businessId => $items)
            @php($business = $items->first()->product->business)

            <section class="overflow-hidden rounded-2xl border border-comb/90 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900/70">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-comb/70 px-4 py-3 dark:border-zinc-800">
                    <a href="{{ route('businesses.show', $business->slug) }}"
                       class="inline-flex items-center gap-2 font-medium transition-colors hover:text-honey-deep dark:hover:text-honey"
                       wire:navigate>
                        <span class="clip-hex grid size-6 place-items-center bg-honey text-[11px] font-bold text-ink">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                        </span>
                        {{ $business->name }}
                    </a>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        Shipped separately by this seller
                    </span>
                </header>

                <ul class="divide-y divide-comb/70 dark:divide-zinc-800">
                    @foreach ($items as $item)
                        <li class="flex flex-wrap items-center gap-4 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('products.show', [$business->slug, $item->product->slug]) }}"
                                   class="font-medium transition-colors hover:text-honey-deep dark:hover:text-honey"
                                   wire:navigate>
                                    {{ $item->product->name }}
                                </a>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->product->formattedPrice() }} each</p>
                            </div>

                            <input type="number"
                                   min="0"
                                   max="{{ $item->product->stock + $item->quantity }}"
                                   value="{{ $item->quantity }}"
                                   wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                   class="w-20 rounded-lg border border-comb bg-paper/60 px-2 py-1.5 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">

                            <span class="w-28 text-end font-semibold">
                                {{ \App\Support\Money::format($item->subtotalMinor(), $item->product->currency) }}
                            </span>

                            <button type="button"
                                    wire:click="remove({{ $item->id }})"
                                    class="text-sm text-zinc-500 transition-colors hover:text-red-600 dark:text-zinc-400">
                                Remove
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-comb/90 bg-white/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/70">
            <div>
                @foreach ($this->subtotals() as $currency => $amount)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Subtotal ({{ $currency }})</p>
                    <p class="font-display text-3xl font-semibold">
                        {{ \App\Support\Money::format($amount, $currency) }}
                    </p>
                @endforeach
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Shipping is added per seller at checkout.</p>
            </div>

            <a href="{{ route('checkout') }}"
               class="rounded-lg bg-ink px-6 py-3 font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
               wire:navigate>
                Checkout
            </a>
        </div>
    @endif
</div>
