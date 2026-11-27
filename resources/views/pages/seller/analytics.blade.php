<?php

use App\Enums\OrderStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Seller analytics')] class extends Component
{
    public string $period = '30';

    public function mount(): void
    {
        $this->authorize('update', $this->business());
    }

    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['7', '30', '90', '365'], true)) {
            return;
        }

        $this->period = $period;
    }

    /**
     * @return array{revenue: int, orders: int, average: int, change: float}
     */
    #[Computed]
    public function summary(): array
    {
        $business = $this->business();
        $days = (int) $this->period;
        $start = now()->subDays($days - 1)->startOfDay();
        $previousStart = now()->subDays(($days * 2) - 1)->startOfDay();

        $orders = $business->orders()->where('status', '!=', OrderStatus::Cancelled);

        $revenue = (int) (clone $orders)->where('created_at', '>=', $start)->sum('total_minor');
        $previousRevenue = (int) (clone $orders)
            ->whereBetween('created_at', [$previousStart, $start])
            ->sum('total_minor');
        $count = (int) (clone $orders)->where('created_at', '>=', $start)->count();

        $change = $previousRevenue > 0
            ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($revenue > 0 ? 100.0 : 0.0);

        return [
            'revenue' => $revenue,
            'orders' => $count,
            'average' => $count > 0 ? (int) round($revenue / $count) : 0,
            'change' => (float) $change,
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, height: int}>
     */
    #[Computed]
    public function series(): array
    {
        $days = (int) $this->period;
        $start = now()->subDays($days - 1)->startOfDay();

        $orders = $this->business()
            ->orders()
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total_minor']);

        if ($days <= 30) {
            $buckets = collect(range($days - 1, 0))->map(function (int $offset) use ($orders, $days): array {
                $date = now()->subDays($offset)->startOfDay();

                return [
                    'label' => $date->format($days <= 7 ? 'D' : 'j M'),
                    'value' => (int) $orders
                        ->filter(fn ($order): bool => $order->created_at?->isSameDay($date) ?? false)
                        ->sum('total_minor'),
                ];
            });
        } else {
            $months = $days === 90 ? 3 : 12;

            $buckets = collect(range($months - 1, 0))->map(function (int $offset) use ($orders): array {
                $date = now()->subMonths($offset)->startOfMonth();

                return [
                    'label' => $date->format('M'),
                    'value' => (int) $orders
                        ->filter(fn ($order): bool => $order->created_at?->format('Y-m') === $date->format('Y-m'))
                        ->sum('total_minor'),
                ];
            });
        }

        $max = max(1, (int) $buckets->max('value'));

        return $buckets
            ->map(fn (array $bucket): array => [
                'label' => $bucket['label'],
                'value' => $bucket['value'],
                'height' => (int) max(6, round(($bucket['value'] / $max) * 100)),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{name: string, quantity: int, revenue: int}>
     */
    #[Computed]
    public function topProducts(): array
    {
        $start = now()->subDays((int) $this->period - 1)->startOfDay();

        $orders = $this->business()
            ->orders()
            ->with('items')
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('created_at', '>=', $start)
            ->get();

        return $orders
            ->flatMap(fn ($order) => $order->items)
            ->groupBy('product_name')
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'quantity' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum('subtotal_minor'),
            ])
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->all();
    }
};
?>

@php
    $business = $this->business();
    $summary = $this->summary();
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Analytics')" :back="route('seller.dashboard')" />

    <div class="hidden lg:flex lg:items-end lg:justify-between lg:gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Seller desk</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Analytics</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">How {{ $business->name }} is trading right now.</p>
        </div>
    </div>

    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0" role="group" aria-label="Analytics period">
        @foreach ([['7', '7D'], ['30', '30D'], ['90', '3M'], ['365', '1Y']] as [$value, $label])
            <button type="button"
                    wire:click="setPeriod('{{ $value }}')"
                    aria-pressed="{{ $period === $value ? 'true' : 'false' }}"
                    class="min-h-9 shrink-0 rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $period === $value ? 'border-ink bg-honey text-ink' : 'border-ink/40 bg-white text-zinc-600 hover:border-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <section class="relative overflow-hidden rounded-[28px] border border-ink/70 bg-hero p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="absolute inset-0 bg-comb opacity-40 dark:opacity-10"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-zinc-600 dark:text-zinc-400">Total Revenue</p>
                <p class="mt-1 font-display text-4xl font-semibold tracking-tight">{{ str_replace(' '.$business->currency, '', \App\Support\Money::format($summary['revenue'], $business->currency)) }}</p>
                <p class="mt-1 text-[11px] font-semibold {{ $summary['change'] >= 0 ? 'text-leaf dark:text-emerald-300' : 'text-red-700 dark:text-red-400' }}">
                    {{ $summary['change'] >= 0 ? '↑ +' : '↓ ' }}{{ number_format($summary['change'], 1) }}% vs previous period
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-center">
                <div class="rounded-xl border border-ink/25 bg-white/70 px-3 py-2 dark:bg-zinc-950">
                    <p class="font-display text-lg font-semibold">{{ number_format($summary['orders']) }}</p>
                    <p class="text-[9px] text-zinc-500 dark:text-zinc-400">Orders</p>
                </div>
                <div class="rounded-xl border border-ink/25 bg-white/70 px-3 py-2 dark:bg-zinc-950">
                    <p class="font-display text-lg font-semibold">{{ str_replace(' '.$business->currency, '', \App\Support\Money::format($summary['average'], $business->currency)) }}</p>
                    <p class="text-[9px] text-zinc-500 dark:text-zinc-400">Avg. order</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-display text-xl font-semibold">Revenue over time</h2>
            <span class="ui-chip">{{ $period === '365' ? '12 months' : ($period === '90' ? '3 months' : $period.' days') }}</span>
        </div>

        @php($series = $this->series())
        @php($hasRevenue = collect($series)->sum('value') > 0)

        @if (! $hasRevenue)
            <div class="mt-5 grid h-48 place-items-center rounded-xl border border-dashed border-ink/25 bg-white/50 text-center dark:border-zinc-700 dark:bg-zinc-950/40">
                <div>
                    <flux:icon name="chart-bar" class="mx-auto size-8 text-zinc-400" />
                    <p class="mt-2 text-sm font-medium">No revenue in this period</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Try a longer range or wait for the next order.</p>
                </div>
            </div>
        @else
            <div class="mt-5 flex h-48 items-end gap-1.5" role="img" aria-label="Revenue chart">
                @foreach ($series as $point)
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1">
                        <div class="w-full rounded-t-md border border-ink/30 bg-honey transition-[height] duration-500"
                             style="height: {{ $point['height'] }}%"
                             title="{{ $point['label'] }}: {{ \App\Support\Money::format($point['value'], $business->currency) }}"></div>
                        @if (count($series) <= 7)
                            <span class="text-[9px] font-medium text-zinc-500 dark:text-zinc-400">{{ $point['label'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if (count($series) > 7)
                <div class="mt-2 flex justify-between text-[9px] font-medium text-zinc-500 dark:text-zinc-400">
                    <span>{{ $series[0]['label'] }}</span>
                    <span>{{ $series[count($series) - 1]['label'] }}</span>
                </div>
            @endif
        @endif
    </section>

    <section class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
        <h2 class="font-display text-xl font-semibold">Top products</h2>

        @if (count($this->topProducts()) === 0)
            <p class="mt-3 rounded-xl border border-dashed border-ink/25 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                No sales in this period yet.
            </p>
        @else
            <ol class="mt-3 space-y-2">
                @foreach ($this->topProducts() as $index => $product)
                    <li class="flex items-center gap-3 rounded-xl border border-ink/25 bg-white/70 p-2.5 dark:border-zinc-800 dark:bg-zinc-950">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full border border-ink/40 bg-honey font-display text-xs font-bold text-ink">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $product['name'] }}</p>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $product['quantity'] }} sold</p>
                        </div>
                        <p class="ui-price shrink-0 text-sm">{{ \App\Support\Money::format($product['revenue'], $business->currency) }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
