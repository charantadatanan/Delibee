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
        <h1 class="text-2xl font-bold tracking-tight">Honey Rewards</h1>
        <p class="mt-1 text-sm text-zinc-500">
            Points you earn from shopping and rating. Every point is recorded in a ledger, so your balance
            is always exact and can be traced back to where it came from.
        </p>
    </div>

    <section class="rounded-xl bg-amber-500 p-6 text-white">
        <p class="text-sm font-medium uppercase tracking-wide opacity-80">Your balance</p>
        <p class="mt-1 text-4xl font-bold">{{ number_format($this->balance()) }}</p>
        <p class="mt-2 text-sm opacity-90">Honey Points</p>
    </section>

    <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <h2 class="border-b border-zinc-200 px-4 py-3 font-semibold dark:border-zinc-700">History</h2>

        @if ($this->entries()->isEmpty())
            <p class="p-8 text-center text-sm text-zinc-500">
                No points yet. Buy something or leave a rating to start earning.
            </p>
        @else
            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->entries() as $entry)
                    <li class="flex items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium">
                                {{ \App\Enums\PointsReason::tryFrom($entry->reason)?->label() ?? $entry->reason }}
                            </p>
                            <p class="text-xs text-zinc-500">{{ $entry->created_at?->format('j M Y, g:i a') }}</p>
                        </div>

                        <span class="font-semibold {{ $entry->delta < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $entry->delta > 0 ? '+' : '' }}{{ number_format($entry->delta) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div>{{ $this->entries()->links() }}</div>
</div>
