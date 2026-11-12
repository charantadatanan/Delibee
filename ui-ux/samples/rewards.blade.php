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

<div class="space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Honey Rewards</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            Points you earn from shopping and rating. Every point is recorded in a ledger, so your balance
            is always exact and can be traced back to where it came from.
        </p>
    </div>

    <section class="relative overflow-hidden rounded-3xl border border-honey/70 bg-honey p-6 text-ink sm:p-8">
        <div class="absolute inset-0 bg-comb opacity-30"></div>

        <div class="relative">
            <p class="text-sm font-medium">Your balance</p>
            <p class="mt-1 font-display text-5xl font-semibold tracking-tight">{{ number_format($this->balance()) }}</p>
            <p class="mt-2 text-sm">Honey Points</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-comb/90 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900/70">
        <h2 class="border-b border-comb/70 px-4 py-3 text-lg font-semibold dark:border-zinc-800">History</h2>

        @if ($this->entries()->isEmpty())
            <p class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                No points yet. Buy something or leave a rating to start earning.
            </p>
        @else
            <ul class="divide-y divide-comb/70 dark:divide-zinc-800">
                @foreach ($this->entries() as $entry)
                    <li class="flex items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium">
                                {{ \App\Enums\PointsReason::tryFrom($entry->reason)?->label() ?? $entry->reason }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $entry->created_at?->format('j M Y, g:i a') }}</p>
                        </div>

                        <span class="font-semibold {{ $entry->delta < 0 ? 'text-red-600 dark:text-red-400' : 'text-leaf dark:text-emerald-300' }}">
                            {{ $entry->delta > 0 ? '+' : '' }}{{ number_format($entry->delta) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div>{{ $this->entries()->links() }}</div>
</div>
