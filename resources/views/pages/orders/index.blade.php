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
        <h1 class="text-2xl font-bold tracking-tight">Bee Flight</h1>
        <p class="mt-1 text-sm text-zinc-500">Order tracking for everything you have bought.</p>
    </div>

    @if ($this->orders()->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <p class="text-lg font-medium">No orders yet.</p>
            <a href="{{ route('marketplace') }}" class="mt-4 inline-block rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white" wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($this->orders() as $order)
                <li>
                    <a href="{{ route('orders.show', $order->number) }}"
                       class="block rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-amber-400 dark:border-zinc-700 dark:bg-zinc-800"
                       wire:navigate>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold">{{ $order->business->name }}</p>
                                <p class="text-xs text-zinc-500">Order {{ $order->number }}</p>
                            </div>

                            @php($statusClasses = match ($order->status) {
                                OrderStatus::Delivered => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
                                OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
                                OrderStatus::Shipped, OrderStatus::OutForDelivery => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-300',
                                default => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
                            })
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                            <p class="text-zinc-500">
                                {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
                                · ordered {{ $order->ordered_at?->format('j M Y') }}
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
