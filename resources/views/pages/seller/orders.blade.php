<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('Seller Orders')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }

    #[Computed]
    public function orders()
    {
        $query = $this->business()->orders()->with(['user', 'items', 'statusEvents.actor'])->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate(20);
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): void
    {
        Gate::authorize('updateStatus', [$order, $newStatus]);

        // Sellers may only advance one step along the delivery timeline.
        if ($newStatus !== $this->nextStatus($order)) {
            return;
        }

        $timestampColumn = $newStatus->timestampColumn();

        $attributes = ['status' => $newStatus];

        if ($timestampColumn !== null) {
            $attributes[$timestampColumn] = now();
        }

        $order->update($attributes);

        $order->statusEvents()->create([
            'status' => $newStatus,
            'note' => $this->statusNote($newStatus),
            'created_by' => auth()->id(),
        ]);

        unset($this->orders);
    }

    private function statusNote(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Confirmed => 'Order confirmed by seller.',
            OrderStatus::Processing => 'Order is being prepared.',
            OrderStatus::Shipped => 'Order has been shipped.',
            OrderStatus::OutForDelivery => 'Order is out for delivery.',
            OrderStatus::Delivered => 'Order delivered to customer.',
            OrderStatus::Cancelled => 'Order cancelled by seller.',
            default => 'Status updated.',
        };
    }

    public function nextStatus(Order $order): ?OrderStatus
    {
        $progression = OrderStatus::progression();
        $currentIndex = array_search($order->status, $progression, true);

        if ($currentIndex === false || $currentIndex >= count($progression) - 1) {
            return null;
        }

        return $progression[$currentIndex + 1];
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Orders</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Manage orders for {{ $this->business()->name }}.</p>
        </div>

        <select wire:model="statusFilter"
                class="rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
            <option value="all">All statuses</option>
            @foreach (App\Enums\OrderStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    @if ($this->orders()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">No orders yet.</p>
            @if ($this->statusFilter !== 'all')
                <a href="{{ route('seller.orders') }}" class="mt-4 inline-block text-sm font-medium text-honey-deep hover:underline dark:text-honey" wire:navigate>
                    View all statuses
                </a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-comb/90 dark:border-zinc-800">
            <table class="w-full">
                <thead class="bg-comb/40 dark:bg-zinc-800">
                    <tr class="text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Ordered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-comb/70 dark:divide-zinc-800">
                    @foreach ($this->orders() as $order)
                        <tr class="transition-colors hover:bg-comb/30 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('seller.orders') }}#order-{{ $order->number }}"
                                   class="font-medium text-honey-deep hover:underline dark:text-honey">
                                    {{ $order->number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $order->user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->user->email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
                            </td>
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
                            <td class="px-4 py-3">
                                @php($next = $this->nextStatus($order))
                                @if ($next)
                                    <button type="button"
                                            wire:click="updateStatus('{{ $order->number }}', '{{ $next->value }}')"
                                            wire:confirm="Move order to {{ $next->label() }}?"
                                            class="rounded-lg bg-ink px-3 py-1.5 text-xs font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
                                            wire:loading.attr="disabled">
                                        {{ $next->label() }}
                                    </button>
                                @else
                                    <span class="text-xs text-zinc-400">Complete</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ $order->ordered_at?->format('j M Y, g:i a') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $this->orders()->links() }}</div>
    @endif
</div>