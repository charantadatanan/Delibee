<?php

use App\Enums\OrderStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('Bee Flight — Your Orders')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function orders()
    {
        return auth()->user()
            ->orders()
            ->with(['business', 'items'])
            ->orderByDesc('created_at')
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Bee Flight</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Order tracking for everything you have bought.</p>
    </div>

    @if ($this->orders()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">No orders yet.</p>
            <a href="{{ route('marketplace') }}"
               class="mt-5 inline-block rounded-lg bg-ink px-4 py-2 font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
               wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($this->orders() as $order)
                <li>
                    <a href="{{ route('orders.show', $order->number) }}"
                       class="block rounded-2xl border border-comb/90 bg-white/70 p-4 transition-colors hover:border-honey-deep/40 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:border-honey/40"
                       wire:navigate>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-display text-lg font-semibold">{{ $order->business->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Order {{ $order->number }}</p>
                            </div>

                            @php($statusClasses = match ($order->status) {
                                OrderStatus::Delivered => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
                                OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                OrderStatus::Shipped, OrderStatus::OutForDelivery => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-300',
                                default => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
                            })
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                            <p class="text-zinc-500 dark:text-zinc-400">
                                {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }},
                                placed {{ $order->ordered_at?->format('j M Y') }}
                            </p>
                            <p class="font-semibold">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $this->orders()->links() }}</div>
    @endif
</div>
