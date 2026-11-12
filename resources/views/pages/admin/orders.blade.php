<?php

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Hive Guard — Orders')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $businessFilter = 'all';

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with(['business', 'user'])
            ->withCount('items')
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->when(
                $this->businessFilter !== 'all',
                fn ($query) => $query->where('business_id', $this->businessFilter),
            )
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    #[Computed]
    public function businesses()
    {
        return Business::query()->orderBy('name')->get(['id', 'name']);
    }

    public function updated(): void
    {
        $this->resetPage();
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Hive Guard</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                Every order across the marketplace. Open an order to review it or cancel it while it is still cancellable.
            </p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Status</span>
                <select wire:model.live="statusFilter"
                        class="mt-1.5 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="all">All statuses</option>
                    @foreach (OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Business</span>
                <select wire:model.live="businessFilter"
                        class="mt-1.5 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="all">All businesses</option>
                    @foreach ($this->businesses() as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($this->orders()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">No orders match this filter.</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Try another status or business.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-comb/90 dark:border-zinc-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-comb/40 dark:bg-zinc-800">
                    <tr class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Ordered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-comb/70 dark:divide-zinc-800">
                    @foreach ($this->orders() as $order)
                        <tr class="transition-colors hover:bg-comb/30 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('orders.show', $order->number) }}"
                                   class="font-medium text-honey-deep hover:underline dark:text-honey">
                                    {{ $order->number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $order->user?->name ?? 'Unknown customer' }}</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $order->user?->email }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $order->business?->name ?? 'Unknown business' }}</td>
                            <td class="px-4 py-3">{{ $order->items_count }}</td>
                            <td class="px-4 py-3 font-semibold">
                                {{ \App\Support\Money::format($order->total_minor, $order->currency) }}
                            </td>
                            <td class="px-4 py-3">
                                @php($statusClasses = match ($order->status) {
                                    OrderStatus::Delivered => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
                                    OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                    OrderStatus::Shipped, OrderStatus::OutForDelivery => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-300',
                                    default => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
                                })
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $order->ordered_at?->format('j M Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $this->orders()->links() }}</div>
    @endif
</div>
