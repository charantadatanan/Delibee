<?php

use App\Exceptions\CheckoutException;
use App\Services\CartService;
use App\Services\CheckoutService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Checkout')] class extends Component
{
    public string $name = '';

    public string $line1 = '';

    public string $city = '';

    public string $postalCode = '';

    public string $phone = '';

    public string $note = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->phone = auth()->user()->profile?->phone ?? '';
    }

    #[Computed]
    public function groups()
    {
        return app(CartService::class)->groups(auth()->user());
    }

    /**
     * Totals grouped by the currency each seller charges in, so a cart that
     * spans currencies never shows a meaningless combined figure.
     *
     * @return array<string, array{subtotal: int, shipping: int, total: int}>
     */
    #[Computed]
    public function totalsByCurrency(): array
    {
        $checkout = app(CheckoutService::class);
        $totals = [];

        foreach ($this->groups() as $items) {
            $currency = $items->first()->product->business->currency;

            $totals[$currency] ??= ['subtotal' => 0, 'shipping' => 0, 'total' => 0];
            $totals[$currency]['subtotal'] += $items->sum(fn ($item) => $item->subtotalMinor());
            $totals[$currency]['shipping'] += $checkout->shippingForCurrency($currency);
        }

        foreach ($totals as $currency => $row) {
            $totals[$currency]['total'] = $row['subtotal'] + $row['shipping'];
        }

        ksort($totals);

        return $totals;
    }

    public function placeOrder(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postalCode' => ['required', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:32'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $orders = app(CheckoutService::class)->place(
                auth()->user(),
                [
                    'name' => $this->name,
                    'line1' => $this->line1,
                    'city' => $this->city,
                    'postal_code' => $this->postalCode,
                    'phone' => $this->phone,
                ],
                $this->note !== '' ? $this->note : null,
            );
        } catch (CheckoutException $exception) {
            \Flux\Flux::toast($exception->getMessage(), variant: 'danger');

            return;
        } catch (\Throwable $exception) {
            report($exception);

            \Flux\Flux::toast('We could not place your order. Please try again.', variant: 'danger');

            return;
        }

        $this->redirect(route('orders.index', array_filter([
            'placed' => 1,
            'order' => $orders->first()?->number,
        ])), navigate: true);
    }
};
?>

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Checkout')" :back="route('cart.index')" />

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Secure checkout</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">One basket, {{ $this->groups()->count() }} {{ \Illuminate\Support\Str::plural('seller', $this->groups()->count()) }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            Your cart is split into one order per seller. Each shop confirms, packs and ships its own parcel.
        </p>
    </div>

    @if ($this->groups()->isEmpty())
        <x-ui.empty-state
            class="mx-auto mt-4 max-w-xl"
            title="There is nothing to check out"
            description="Your Honey Pot is empty. Find something from a local seller first."
            icon="shopping-bag">
            <a href="{{ route('marketplace') }}" class="ui-btn" wire:navigate>Browse the marketplace</a>
        </x-ui.empty-state>
    @else
        <form wire:submit="placeOrder" class="grid gap-5 lg:grid-cols-[1fr_360px] lg:items-start">
            <div class="space-y-4">
                <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full border border-ink/50 bg-honey">
                            <flux:icon name="map-pin" class="size-5 text-ink" />
                        </span>
                        <div>
                            <h2 class="font-display text-xl font-semibold">Delivery Address</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Where should the hive deliver?</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="ui-label">Full name</span>
                            <input type="text" wire:model="name" autocomplete="name" class="ui-field mt-1.5" @error('name') aria-invalid="true" @enderror>
                            @error('name') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="ui-label">Street address</span>
                            <input type="text" wire:model="line1" autocomplete="street-address" class="ui-field mt-1.5" @error('line1') aria-invalid="true" @enderror>
                            @error('line1') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="ui-label">City</span>
                            <input type="text" wire:model="city" autocomplete="address-level2" class="ui-field mt-1.5" @error('city') aria-invalid="true" @enderror>
                            @error('city') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="ui-label">Postal code</span>
                            <input type="text" wire:model="postalCode" autocomplete="postal-code" class="ui-field mt-1.5" @error('postalCode') aria-invalid="true" @enderror>
                            @error('postalCode') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="ui-label">Contact number</span>
                            <input type="tel" wire:model="phone" autocomplete="tel" class="ui-field mt-1.5" @error('phone') aria-invalid="true" @enderror>
                            @error('phone') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="ui-label">Note for the sellers (optional)</span>
                            <textarea wire:model="note" rows="3" class="ui-field mt-1.5" placeholder="Gate code, delivery instructions, a gift note…"></textarea>
                            @error('note') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                        </label>
                    </div>
                </section>

                <section class="ui-card bg-paper dark:bg-zinc-900">
                    <header class="flex items-center justify-between gap-3 border-b border-ink/15 p-4 dark:border-zinc-800">
                        <div>
                            <h2 class="font-display text-xl font-semibold">Order Items</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Split automatically by seller</p>
                        </div>
                        <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>Edit</a>
                    </header>

                    <div class="divide-y divide-ink/10 dark:divide-zinc-800">
                        @foreach ($this->groups() as $items)
                            @php($business = $items->first()->product->business)

                            <div class="p-4">
                                <div class="flex items-center gap-2 text-xs font-semibold">
                                    <span class="grid size-6 place-items-center rounded-full border border-ink/40 bg-honey text-[9px] font-bold text-ink">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                                    </span>
                                    {{ $business->name }}
                                    <span class="ms-auto font-normal text-zinc-500 dark:text-zinc-400">Ships separately</span>
                                </div>

                                <ul class="mt-3 space-y-3">
                                    @foreach ($items as $item)
                                        <li class="flex items-center gap-3">
                                            <span class="relative grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-ink/40 bg-hero dark:border-zinc-700 dark:bg-zinc-950">
                                                @if ($item->product->images->isNotEmpty())
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item->product->images->first()->path) }}" alt="" loading="lazy" class="size-full object-cover">
                                                @else
                                                    <span class="font-display text-lg font-semibold text-honey-deep dark:text-honey">
                                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->product->name, 0, 1)) }}
                                                    </span>
                                                @endif
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <p class="line-clamp-2 text-sm font-medium leading-tight">{{ $item->product->name }}</p>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                    Qty {{ $item->quantity }} · {{ $item->product->formattedPrice() }} each
                                                </p>
                                            </div>

                                            <p class="ui-price shrink-0 text-sm">
                                                {{ \App\Support\Money::format($item->subtotalMinor(), $item->product->currency) }}
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="ui-card-honey flex items-start gap-3 p-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full border border-ink/40 bg-white/70">
                        <flux:icon name="wallet" class="size-5 text-ink" />
                    </span>
                    <div>
                        <p class="font-semibold">Payment Method</p>
                        <p class="mt-1 text-sm text-zinc-700">
                            Payment is on hold while the gateway is connected. Your order is recorded now and each
                            seller is paid directly once payments go live.
                        </p>
                    </div>
                </div>
            </div>

            <aside class="ui-card bg-paper p-4 dark:bg-zinc-900 lg:sticky lg:top-24">
                <h2 class="font-display text-xl font-semibold">Payment Details</h2>

                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($this->groups() as $items)
                        @php($business = $items->first()->product->business)
                        <li class="flex items-center justify-between gap-3">
                            <span class="truncate text-zinc-600 dark:text-zinc-400">{{ $business->name }}</span>
                            <span class="shrink-0 font-medium">
                                {{ \App\Support\Money::format($items->sum(fn ($item) => $item->subtotalMinor()), $business->currency) }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 border-t border-ink/15 pt-4 text-sm dark:border-zinc-800">
                    @foreach ($this->totalsByCurrency() as $currency => $totals)
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Subtotal ({{ $currency }})</dt>
                            <dd>{{ \App\Support\Money::format($totals['subtotal'], $currency) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Shipping ({{ $currency }})</dt>
                            <dd>{{ \App\Support\Money::format($totals['shipping'], $currency) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1 text-base font-semibold">
                            <dt>Total ({{ $currency }})</dt>
                            <dd class="ui-price">{{ \App\Support\Money::format($totals['total'], $currency) }}</dd>
                        </div>
                    @endforeach
                </dl>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="ui-btn mt-5 w-full">
                    <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                    <span wire:loading wire:target="placeOrder">Placing…</span>
                </button>

                <p class="mt-3 text-center text-[10px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                    By placing this order you agree to the deliBEE terms. Payment is not collected yet.
                </p>
            </aside>
        </form>
    @endif
</div>
