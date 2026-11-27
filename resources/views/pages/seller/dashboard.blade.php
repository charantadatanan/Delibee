<?php

use App\Enums\OrderStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Seller dashboard')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('update', $this->business());
    }

    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }

    /**
     * @return array{orders: int, customers: int, active: int, revenue: int, revenue_change: float, low_stock: int}
     */
    #[Computed]
    public function stats(): array
    {
        $business = $this->business();
        $orders = $business->orders();

        $currentStart = now()->subDays(30)->startOfDay();
        $previousStart = now()->subDays(60)->startOfDay();

        $revenueOrders = (clone $orders)->where('status', '!=', OrderStatus::Cancelled);
        $currentRevenue = (int) (clone $revenueOrders)->where('created_at', '>=', $currentStart)->sum('total_minor');
        $previousRevenue = (int) (clone $revenueOrders)
            ->whereBetween('created_at', [$previousStart, $currentStart])
            ->sum('total_minor');

        $change = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100.0 : 0.0);

        return [
            'orders' => (int) (clone $orders)->count(),
            'customers' => (int) (clone $orders)->distinct('user_id')->count('user_id'),
            'active' => (int) (clone $orders)->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
                OrderStatus::Shipped,
                OrderStatus::OutForDelivery,
            ])->count(),
            'revenue' => (int) (clone $revenueOrders)->sum('total_minor'),
            'revenue_change' => (float) $change,
            'low_stock' => (int) $business->products()->where('stock', '<=', 5)->count(),
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, height: int}>
     */
    #[Computed]
    public function salesSeries(): array
    {
        $start = now()->subDays(6)->startOfDay();

        $orders = $this->business()
            ->orders()
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total_minor']);

        $days = collect(range(6, 0))->map(function (int $offset) use ($orders): array {
            $date = now()->subDays($offset)->startOfDay();
            $value = (int) $orders
                ->filter(fn ($order): bool => $order->created_at?->isSameDay($date) ?? false)
                ->sum('total_minor');

            return ['label' => $date->format('D'), 'value' => $value, 'height' => 0];
        });

        $max = max(1, (int) $days->max('value'));

        return $days
            ->map(fn (array $day): array => [
                'label' => $day['label'],
                'value' => $day['value'],
                'height' => (int) max(6, round(($day['value'] / $max) * 100)),
            ])
            ->all();
    }

    #[Computed]
    public function stockAlerts()
    {
        return $this->business()
            ->products()
            ->with('images')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(3)
            ->get();
    }

    #[Computed]
    public function recentOrders()
    {
        return $this->business()
            ->orders()
            ->with(['user', 'items'])
            ->latest('created_at')
            ->take(4)
            ->get();
    }
};
?>

@php
    $business = $this->business();
    $stats = $this->stats();
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Dashboard')" :back="route('marketplace')">
        <x-slot:actions>
            <a href="{{ route('seller.orders') }}" class="grid size-9 place-items-center rounded-full border border-ink/50 bg-white dark:border-zinc-700 dark:bg-zinc-900" aria-label="Seller orders" wire:navigate>
                <flux:icon name="bell" class="size-4" />
            </a>
        </x-slot:actions>
    </x-ui.mobile-header>

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Seller desk</p>
            <h1 class="mt-1 font-display text-3xl font-semibold tracking-tight sm:text-4xl">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Welcome back, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}!</p>
        </div>

        <a href="{{ route('seller.products') }}" class="ui-btn ui-btn-sm" wire:navigate>
            <flux:icon name="plus" class="size-4" />
            Add product
        </a>
    </div>

    {{-- Key numbers --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Total Orders', 'value' => number_format($stats['orders']), 'icon' => 'shopping-cart', 'delta' => null],
                ['label' => 'Customers', 'value' => number_format($stats['customers']), 'icon' => 'users', 'delta' => null],
                ['label' => 'Active Orders', 'value' => number_format($stats['active']), 'icon' => 'bolt', 'delta' => null],
                ['label' => 'Total Revenue', 'value' => str_replace(' '.$business->currency, '', \App\Support\Money::format($stats['revenue'], $business->currency)), 'icon' => 'banknotes', 'delta' => $stats['revenue_change']],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="ui-card bg-paper p-3.5 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-2">
                    <span class="grid size-10 place-items-center rounded-full border border-ink/50 bg-honey text-ink">
                        <flux:icon name="{{ $card['icon'] }}" class="size-5" />
                    </span>

                    @if ($card['delta'] !== null)
                        <span class="text-[10px] font-semibold {{ $card['delta'] >= 0 ? 'text-leaf dark:text-emerald-300' : 'text-red-700 dark:text-red-400' }}">
                            {{ $card['delta'] >= 0 ? '↑ +' : '↓ ' }}{{ number_format($card['delta'], 1) }}%
                        </span>
                    @endif
                </div>

                <p class="mt-3 text-[10px] font-medium text-zinc-600 dark:text-zinc-400">{{ $card['label'] }}</p>
                <p class="mt-0.5 font-display text-2xl font-semibold leading-tight">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid gap-4 lg:grid-cols-[1.35fr_1fr]">
        {{-- Sales overview --}}
        <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold">Sales Overview</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Last 7 days</p>
                </div>
                <span class="ui-chip">This week</span>
            </div>

            @php($series = $this->salesSeries())
            @php($hasSales = collect($series)->sum('value') > 0)

            @if (! $hasSales)
                <div class="mt-5 grid h-44 place-items-center rounded-xl border border-dashed border-ink/25 bg-white/50 text-center dark:border-zinc-700 dark:bg-zinc-950/40">
                    <div>
                        <flux:icon name="chart-bar" class="mx-auto size-8 text-zinc-400" />
                        <p class="mt-2 text-sm font-medium">No sales in the last 7 days</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">New orders will show up here.</p>
                    </div>
                </div>
            @else
                <div class="mt-5 flex h-44 items-end gap-2" role="img" aria-label="Sales for the last seven days">
                    @foreach ($series as $day)
                        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1.5">
                            <span class="text-[9px] font-semibold text-zinc-600 dark:text-zinc-400">
                                {{ $day['value'] > 0 ? \App\Support\Money::format($day['value'], $business->currency) : '' }}
                            </span>
                            <div class="w-full rounded-t-lg border border-ink/40 bg-honey transition-[height] duration-500"
                                 style="height: {{ $day['height'] }}%"
                                 title="{{ $day['label'] }}: {{ \App\Support\Money::format($day['value'], $business->currency) }}"></div>
                            <span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 border-t border-ink/10 pt-3 text-xs text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                {{ $stats['orders'] }} orders all time · {{ \App\Support\Money::format($stats['revenue'], $business->currency) }} recorded. Payments are on hold.
            </div>
        </section>

        {{-- Stock alerts --}}
        <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold">Stock Alerts</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $stats['low_stock'] }} {{ \Illuminate\Support\Str::plural('item', $stats['low_stock']) }} running low</p>
                </div>
                <a href="{{ route('seller.products') }}" class="text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>View All →</a>
            </div>

            @if ($this->stockAlerts()->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-ink/25 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    Every shelf is stocked. Nice.
                </div>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($this->stockAlerts() as $product)
                        <li class="flex items-center gap-3 rounded-xl border border-ink/25 bg-white/70 p-2.5 dark:border-zinc-800 dark:bg-zinc-950">
                            <span class="relative grid size-12 shrink-0 place-items-center overflow-hidden rounded-lg border border-ink/30 bg-hero dark:border-zinc-700 dark:bg-zinc-900">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}" alt="" loading="lazy" class="size-full object-cover">
                                @else
                                    <span class="font-display text-lg font-semibold text-honey-deep dark:text-honey">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                    </span>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $product->name }}</p>
                                <p class="text-[10px] font-medium {{ $product->stock <= 2 ? 'text-red-700 dark:text-red-400' : 'text-[#9A6A00] dark:text-[#E0B34A]' }}">
                                    Only {{ $product->stock }} left in stock
                                </p>
                            </div>

                            <a href="{{ route('seller.products', ['restock' => $product->id]) }}" class="ui-btn ui-btn-sm shrink-0" wire:navigate>Restock</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- Recent orders --}}
    @if ($this->recentOrders()->isNotEmpty())
        <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold">Recent orders</h2>
                <a href="{{ route('seller.orders') }}" class="text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>All orders</a>
            </div>

            <ul class="mt-3 divide-y divide-ink/10 dark:divide-zinc-800">
                @foreach ($this->recentOrders() as $order)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $order->user->name }}</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $order->number }} · {{ $order->created_at?->format('M j, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.order-status :status="$order->status" />
                            <span class="ui-price text-sm">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
