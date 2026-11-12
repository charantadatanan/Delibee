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

        $this->redirect(route('orders.index'), navigate: true);
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Checkout</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            Your cart will be split into {{ $this->groups()->count() }} {{ \Illuminate\Support\Str::plural('order', $this->groups()->count()) }},
            one per seller, each shipped separately.
        </p>
    </div>

    @if ($this->groups()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">There is nothing to check out.</p>
            <a href="{{ route('marketplace') }}"
               class="mt-5 inline-block rounded-lg bg-ink px-4 py-2 font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
               wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <section class="rounded-2xl border border-comb/90 bg-white/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70">
                <h2 class="font-semibold">Delivery address</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Full name</span>
                        <input type="text" wire:model="name"
                               class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Street address</span>
                        <input type="text" wire:model="line1"
                               class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">City</span>
                        <input type="text" wire:model="city"
                               class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Postal code</span>
                        <input type="text" wire:model="postalCode"
                               class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Contact number</span>
                        <input type="tel" wire:model="phone"
                               class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Note for the sellers (optional)</span>
                        <textarea wire:model="note" rows="3"
                                  class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                    </label>
                </div>

                <div class="mt-6 rounded-xl bg-comb/40 p-4 text-sm dark:bg-zinc-950/60">
                    <p class="font-medium">Payment</p>
                    <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                        The payment method is not set up yet. Each order is settled in the seller's currency,
                        recorded now, and paid to the seller directly once the gateway is connected.
                    </p>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-comb/90 bg-white/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70">
                <h2 class="font-semibold">Order summary</h2>

                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($this->groups() as $items)
                        @php($business = $items->first()->product->business)
                        <li class="flex justify-between gap-3">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $business->name }}</span>
                            <span>{{ $items->sum(fn ($item) => $item->subtotalMinor()) ? \App\Support\Money::format($items->sum(fn ($item) => $item->subtotalMinor()), $business->currency) : '—' }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-1 border-t border-comb/70 pt-4 text-sm dark:border-zinc-700">
                    @foreach ($this->totalsByCurrency() as $currency => $totals)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Subtotal ({{ $currency }})</dt>
                            <dd>{{ \App\Support\Money::format($totals['subtotal'], $currency) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Shipping ({{ $currency }})</dt>
                            <dd>{{ \App\Support\Money::format($totals['shipping'], $currency) }}</dd>
                        </div>
                        <div class="flex justify-between pt-1 text-base font-semibold">
                            <dt>Total ({{ $currency }})</dt>
                            <dd>{{ \App\Support\Money::format($totals['total'], $currency) }}</dd>
                        </div>
                    @endforeach
                </dl>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="mt-5 w-full rounded-lg bg-ink px-4 py-2.5 font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                    <span wire:loading.remove wire:target="placeOrder">Place order</span>
                    <span wire:loading wire:target="placeOrder">Placing…</span>
                </button>
            </aside>
        </form>
    @endif
</div>
