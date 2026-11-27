<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('Bee Flight — Your Orders')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Computed]
    public function orders()
    {
        $query = auth()->user()
            ->orders()
            ->with(['business', 'items.product.images'])
            ->orderByDesc('created_at');

        $this->applyStatusFilter($query);

        return $query->paginate(15);
    }

    #[Computed]
    public function placedOrder(): ?Order
    {
        $number = request()->query('order');

        if (! request()->boolean('placed') || ! is_string($number) || $number === '') {
            return null;
        }

        return auth()->user()
            ->orders()
            ->with('items.product')
            ->where('number', $number)
            ->first();
    }

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'processing', 'shipped', 'delivered'], true)) {
            return;
        }

        $this->statusFilter = $filter;
        $this->resetPage();
    }

    private function applyStatusFilter(HasMany $query): void
    {
        match ($this->statusFilter) {
            'processing' => $query->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
            ]),
            'shipped' => $query->whereIn('status', [
                OrderStatus::Shipped,
                OrderStatus::OutForDelivery,
            ]),
            'delivered' => $query->where('status', OrderStatus::Delivered),
            default => null,
        };
    }
};
?>

<div class="space-y-5">
    <x-ui.mobile-header :title="__('My Orders')" :back="route('dashboard')" />

    @if (request()->boolean('placed'))
        @php($placed = $this->placedOrder())

        <section class="ui-card-honey overflow-hidden p-5 text-center sm:p-8">
            <div class="relative mx-auto grid size-28 place-items-center">
                <span class="absolute inset-0 rounded-full bg-[#7DC24E]/25"></span>
                <span class="relative grid size-24 place-items-center rounded-full border-[3px] border-[#3E7A2A] bg-[#6CB53E] text-white shadow-[inset_0_-6px_0_rgba(0,0,0,0.15)]">
                    <flux:icon name="check" class="size-12" stroke-width="3" />
                </span>
                <img src="{{ asset('images/mobile/bee-welcome.png') }}"
                     alt=""
                     class="absolute -end-3 bottom-0 h-14 w-auto rotate-[-8deg] drop-shadow-sm">
                <span class="absolute left-0 top-1 text-xl text-honey-deep" aria-hidden="true">✦</span>
                <span class="absolute bottom-2 left-2 text-sm text-honey-deep" aria-hidden="true">✦</span>
            </div>

            <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight">Order Confirmed!</h1>

            @if ($placed)
                <p class="mt-2 text-sm font-medium">Order ID: {{ $placed->number }}</p>
                <p class="mt-1 text-sm text-zinc-700">
                    Estimated Delivery: {{ $placed->ordered_at?->copy()->addDays(3)->format('F j') ?? 'in 3 days' }}
                </p>
            @else
                <p class="mt-2 text-sm text-zinc-700">Your order is in the hive.</p>
            @endif

            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                @if ($placed)
                    <a href="{{ route('orders.show', $placed->number) }}" class="ui-btn" wire:navigate>View Order Details</a>
                @endif
                <a href="{{ route('marketplace') }}" class="ui-btn ui-btn-ghost" wire:navigate>Back to Home</a>
            </div>
        </section>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Bee Flight</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">My Orders</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Everything you have bought, tracked per seller.</p>
        </div>

        <a href="{{ route('marketplace') }}" class="ms-auto hidden text-sm font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey lg:inline" wire:navigate>
            Continue shopping →
        </a>
    </div>

    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0" role="group" aria-label="Filter orders">
        @foreach ([['all', 'All'], ['processing', 'Processing'], ['shipped', 'Shipped'], ['delivered', 'Delivered']] as [$key, $label])
            <button type="button"
                    wire:click="setFilter('{{ $key }}')"
                    aria-pressed="{{ $statusFilter === $key ? 'true' : 'false' }}"
                    class="min-h-9 shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition {{ $statusFilter === $key ? 'border-ink bg-honey text-ink' : 'border-ink/40 bg-white text-zinc-600 hover:border-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($this->orders()->isEmpty())
        <x-ui.empty-state
            title="No orders in this nest"
            description="When you place your first order it will show up here with its own flight path."
            icon="cube">
            <a href="{{ route('marketplace') }}" class="ui-btn" wire:navigate>Browse the marketplace</a>
        </x-ui.empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($this->orders() as $order)
                @php($firstItem = $order->items->first())

                <li>
                    <a href="{{ route('orders.show', $order->number) }}"
                       class="group ui-card block bg-paper p-3 transition duration-200 hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900 sm:p-4"
                       wire:navigate>
                        <div class="flex items-start gap-3">
                            <span class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-ink/50 bg-hero dark:border-zinc-700 dark:bg-zinc-950">
                                @if ($firstItem?->product?->images?->isNotEmpty())
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($firstItem->product->images->first()->path) }}"
                                         alt=""
                                         loading="lazy"
                                         class="size-full object-cover">
                                @else
                                    <span class="font-display text-xl font-semibold text-honey-deep dark:text-honey">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($firstItem->product_name ?? $order->business->name, 0, 1)) }}
                                    </span>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate font-display text-base font-semibold">{{ $order->business->name }}</p>
                                    <x-ui.order-status :status="$order->status" class="ms-auto" />
                                </div>

                                <p class="mt-1 line-clamp-2 text-sm font-medium leading-tight">
                                    {{ $firstItem?->product_name ?? 'Order items' }}
                                    @if ($order->items->count() > 1)
                                        <span class="font-normal text-zinc-500 dark:text-zinc-400">+{{ $order->items->count() - 1 }} more</span>
                                    @endif
                                </p>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                    <span>Order {{ $order->number }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $order->ordered_at?->format('M j, Y') }}</span>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <p class="ui-price text-base">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</p>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-honey-deep dark:text-honey">
                                        Track
                                        <flux:icon name="chevron-right" class="size-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-5">{{ $this->orders()->links() }}</div>
    @endif
</div>
