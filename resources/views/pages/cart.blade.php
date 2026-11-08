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

    #[Computed]
    public function subtotalMinor(): int
    {
        return app(CartService::class)->subtotalMinor(auth()->user());
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->updateQuantity(auth()->user(), $item, $quantity);

        unset($this->groups, $this->subtotalMinor);
    }

    public function remove(int $itemId): void
    {
        $item = auth()->user()->cartItems()->findOrFail($itemId);

        app(CartService::class)->remove(auth()->user(), $item);

        unset($this->groups, $this->subtotalMinor);
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Honey Pot</h1>
        <p class="mt-1 text-sm text-zinc-500">Your cart. Checkout will split it into one order per seller.</p>
    </div>

    @if ($this->groups()->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <p class="text-lg font-medium">Your Honey Pot is empty.</p>
            <p class="mt-1 text-sm text-zinc-500">Find something good in the marketplace.</p>
            <a href="{{ route('marketplace') }}"
               class="mt-4 inline-block rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white hover:bg-amber-600"
               wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        @foreach ($this->groups() as $businessId => $items)
            @php($business = $items->first()->product->business)

            <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <header class="flex items-center justify-between gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <a href="{{ route('businesses.show', $business->slug) }}"
                       class="font-semibold hover:text-amber-600"
                       wire:navigate>
                        {{ $business->name }}
                    </a>
                    <span class="text-xs text-zinc-500">
                        Shipped separately by this seller
                    </span>
                </header>

                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($items as $item)
                        <li class="flex flex-wrap items-center gap-4 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('products.show', [$business->slug, $item->product->slug]) }}"
                                   class="font-medium hover:text-amber-600"
                                   wire:navigate>
                                    {{ $item->product->name }}
                                </a>
                                <p class="text-xs text-zinc-500">{{ $item->product->formattedPrice() }} each</p>
                            </div>

                            <input type="number"
                                   min="0"
                                   max="{{ $item->product->stock + $item->quantity }}"
                                   value="{{ $item->quantity }}"
                                   wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                   class="w-20 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-900">

                            <span class="w-28 text-end font-semibold">
                                {{ \App\Support\Money::format($item->subtotalMinor(), $item->product->currency) }}
                            </span>

                            <button type="button"
                                    wire:click="remove({{ $item->id }})"
                                    class="text-sm text-zinc-500 hover:text-red-600">
                                Remove
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div>
                <p class="text-sm text-zinc-500">Subtotal</p>
                <p class="text-2xl font-bold">
                    {{ \App\Support\Money::format($this->subtotalMinor(), config('commerce.currency')) }}
                </p>
                <p class="text-xs text-zinc-500">Shipping is added per seller at checkout.</p>
            </div>

            <a href="{{ route('checkout') }}"
               class="rounded-lg bg-amber-500 px-6 py-3 font-semibold text-white hover:bg-amber-600"
               wire:navigate>
                Checkout
            </a>
        </div>
    @endif
</div>
