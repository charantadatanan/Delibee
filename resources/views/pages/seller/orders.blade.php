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

    public function setFilter(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }

    #[Computed]
    public function orders()
    {
        $query = $this->business()->orders()->with(['user', 'items.product'])->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate(20);
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): void
    {
        Gate::authorize('updateStatus', [$order, $newStatus]);

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

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Orders')" :back="route('seller.dashboard')">
        <x-slot:actions>
            <span class="rounded-full border border-ink/30 bg-white px-2 py-1 text-[9px] font-semibold dark:border-zinc-700 dark:bg-zinc-900">
                {{ $this->orders()->total() }}
            </span>
        </x-slot:actions>
    </x-ui.mobile-header>

    <div class="hidden lg:flex lg:items-end lg:justify-between lg:gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Seller desk</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Orders</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Move each order one step along its flight path.</p>
        </div>
    </div>

    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0" role="group" aria-label="Filter orders by status">
        <button type="button"
                wire:click="setFilter('all')"
                aria-pressed="{{ $statusFilter === 'all' ? 'true' : 'false' }}"
                class="min-h-9 shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'all' ? 'border-ink bg-honey text-ink' : 'border-ink/40 bg-white text-zinc-600 hover:border-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
            All
        </button>

        @foreach (App\Enums\OrderStatus::cases() as $status)
            <button type="button"
                    wire:click="setFilter('{{ $status->value }}')"
                    aria-pressed="{{ $statusFilter === $status->value ? 'true' : 'false' }}"
                    class="min-h-9 shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition {{ $statusFilter === $status->value ? 'border-ink bg-honey text-ink' : 'border-ink/40 bg-white text-zinc-600 hover:border-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                {{ $status->label() }}
            </button>
        @endforeach
    </div>

    @if ($this->orders()->isEmpty())
        <x-ui.empty-state
            title="No orders in this nest"
            description="Orders appear here as soon as a customer checks out."
            icon="cube">
            @if ($statusFilter !== 'all')
                <button type="button" wire:click="setFilter('all')" class="ui-btn">View all statuses</button>
            @endif
        </x-ui.empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($this->orders() as $order)
                <li class="ui-card bg-paper p-4 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-display text-lg font-semibold">{{ $order->number }}</p>
                                <x-ui.order-status :status="$order->status" />
                            </div>
                            <p class="mt-1 text-sm font-medium">{{ $order->user->name }}</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $order->user->email }}</p>
                        </div>

                        <div class="text-end">
                            <p class="ui-price text-lg">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-ink/10 pt-3 dark:border-zinc-800">
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Ordered {{ $order->ordered_at?->format('M j, Y · g:i a') }}
                        </p>

                        <div class="flex items-center gap-2">
                            @php($next = $this->nextStatus($order))
                            @if ($next)
                                <button type="button"
                                        wire:click="updateStatus('{{ $order->number }}', '{{ $next->value }}')"
                                        wire:confirm="Move order to {{ $next->label() }}?"
                                        wire:loading.attr="disabled"
                                        class="ui-btn ui-btn-sm">
                                    Mark {{ $next->label() }}
                                </button>
                            @else
                                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Flight complete</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-5">{{ $this->orders()->links() }}</div>
    @endif
</div>
