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

    #[Computed]
    public function subtotalMinor(): int
    {
        return app(CartService::class)->subtotalMinor(auth()->user());
    }

    #[Computed]
    public function shippingMinor(): int
    {
        $groups = $this->groups();

        return $groups->count() * (int) config('commerce.flat_shipping_minor', 0);
    }

    #[Computed]
    public function totalMinor(): int
    {
        return $this->subtotalMinor() + $this->shippingMinor();
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
        <h1 class="text-2xl font-bold tracking-tight">Checkout</h1>
        <p class="mt-1 text-sm text-zinc-500">
            Your cart will be split into {{ $this->groups()->count() }} {{ \Illuminate\Support\Str::plural('order', $this->groups()->count()) }} —
            one per seller, each shipped separately.
        </p>
    </div>

    @if ($this->groups()->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <p class="text-lg font-medium">There is nothing to check out.</p>
            <a href="{{ route('marketplace') }}" class="mt-4 inline-block rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white" wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="font-semibold">Delivery address</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Full name</span>
                        <input type="text" wire:model="name"
                               class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Street address</span>
                        <input type="text" wire:model="line1"
                               class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">City</span>
                        <input type="text" wire:model="city"
                               class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Postal code</span>
                        <input type="text" wire:model="postalCode"
                               class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Contact number</span>
                        <input type="tel" wire:model="phone"
                               class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Note for the sellers (optional)</span>
                        <textarea wire:model="note" rows="3"
                                  class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900"></textarea>
                    </label>
                </div>

                <div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-900">
                    <p class="font-medium">Payment</p>
                    <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                        The payment method is not set up yet — the launch country and the seller payout model
                        are still open decisions. Your order is recorded now and you will pay the sellers
                        directly once that is settled.
                    </p>
                </div>
            </section>

            <aside class="h-fit rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
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

                <dl class="mt-4 space-y-1 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Subtotal</dt>
                        <dd>{{ \App\Support\Money::format($this->subtotalMinor(), config('commerce.currency')) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Shipping</dt>
                        <dd>{{ \App\Support\Money::format($this->shippingMinor(), config('commerce.currency')) }}</dd>
                    </div>
                    <div class="flex justify-between pt-1 text-base font-semibold">
                        <dt>Total</dt>
                        <dd>{{ \App\Support\Money::format($this->totalMinor(), config('commerce.currency')) }}</dd>
                    </div>
                </dl>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="mt-5 w-full rounded-lg bg-amber-500 px-4 py-2.5 font-semibold text-white hover:bg-amber-600 disabled:opacity-50">
                    <span wire:loading.remove wire:target="placeOrder">Place order</span>
                    <span wire:loading wire:target="placeOrder">Placing…</span>
                </button>
            </aside>
        </form>
    @endif
</div>
