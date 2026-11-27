<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('Honey Rewards')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function entries()
    {
        return auth()->user()
            ->pointsLedger()
            ->latest('created_at')
            ->paginate(25);
    }

    #[Computed]
    public function balance(): int
    {
        return auth()->user()->pointsBalance();
    }
};
?>

@php
    $balance = $this->balance();
    $tier = \App\Support\BeeTier::for($balance);
    $ladder = [
        ['name' => 'Larva', 'at' => 0, 'badge' => 'bg-[#FFE7A3]'],
        ['name' => 'Forager', 'at' => 300, 'badge' => 'bg-[#FFC233]'],
        ['name' => 'Courtier', 'at' => 1000, 'badge' => 'bg-[#F3B6A0]'],
        ['name' => 'Queen', 'at' => 2500, 'badge' => 'bg-[#CDB6F2]'],
    ];
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Honey Rewards')" :back="route('dashboard')" />

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Honey Points</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Honey Rewards</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            Points you earn from shopping and rating. Every point is recorded in a ledger, so your balance is exact
            and can be traced back to where it came from.
        </p>
    </div>

    <section class="relative overflow-hidden rounded-[28px] border border-ink/70 bg-honey p-5 text-ink dark:border-zinc-800 sm:p-6">
        <div class="absolute inset-0 bg-comb opacity-30"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em]">Your balance</p>
                <p class="mt-1 font-display text-5xl font-semibold tracking-tight">{{ number_format($balance) }}</p>
                <p class="mt-1 text-sm">Honey Points</p>
            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-ink/50 px-3 py-1.5 text-xs font-bold {{ $tier['badge'] }}">
                <span aria-hidden="true">♛</span>
                {{ $tier['name'] }} tier
            </span>
        </div>

        <div class="relative mt-5">
            <div class="flex items-center justify-between text-[11px] font-medium">
                <span>{{ $tier['name'] }}</span>
                @if ($tier['next'])
                    <span>{{ number_format($tier['next_at'] - $balance) }} points to {{ $tier['next'] }}</span>
                @else
                    <span>Top of the hive</span>
                @endif
            </div>
            <div class="mt-1.5 h-3 overflow-hidden rounded-full border border-ink/40 bg-paper/80">
                <div class="h-full rounded-full bg-ink" style="width: {{ $tier['progress'] }}%"></div>
            </div>
        </div>
    </section>

    <section aria-label="Honey Rewards tiers">
        <h2 class="font-display text-xl font-semibold">The four tiers</h2>
        <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ($ladder as $row)
                @php($active = $tier['name'] === $row['name'])
                <div class="rounded-2xl border p-4 {{ $active ? 'border-ink bg-hero dark:border-zinc-600 dark:bg-zinc-800' : 'border-ink/25 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900' }}">
                    <span class="inline-flex items-center gap-1 rounded-full border border-ink/30 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $row['badge'] }} text-ink">
                        {{ $row['name'] }}
                    </span>
                    <p class="mt-2 font-display text-xl font-semibold">{{ number_format($row['at']) }}+</p>
                    <p class="text-[10px] text-zinc-500 dark:text-zinc-400">Honey Points</p>
                    @if ($active)
                        <p class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-honey-deep dark:text-honey">
                            <span class="size-1.5 rounded-full bg-leaf" aria-hidden="true"></span>
                            Your tier
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="ui-card overflow-hidden bg-paper dark:bg-zinc-900">
        <h2 class="border-b border-ink/15 px-4 py-3 font-display text-xl font-semibold dark:border-zinc-800">History</h2>

        @if ($this->entries()->isEmpty())
            <p class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                No points yet. Buy something or leave a rating to start earning.
            </p>
        @else
            <ul class="divide-y divide-ink/10 dark:divide-zinc-800">
                @foreach ($this->entries() as $entry)
                    <li class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full border border-ink/25 bg-white/70 dark:border-zinc-700 dark:bg-zinc-950">
                                <flux:icon name="{{ $entry->delta < 0 ? 'arrow-down-left' : 'sparkles' }}" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ \App\Enums\PointsReason::tryFrom($entry->reason)?->label() ?? $entry->reason }}
                                </p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $entry->created_at?->format('j M Y, g:i a') }}</p>
                            </div>
                        </div>

                        <span class="ui-price shrink-0 text-sm {{ $entry->delta < 0 ? 'text-red-700 dark:text-red-400' : 'text-leaf dark:text-emerald-300' }}">
                            {{ $entry->delta > 0 ? '+' : '' }}{{ number_format($entry->delta) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div>{{ $this->entries()->links() }}</div>
</div>
