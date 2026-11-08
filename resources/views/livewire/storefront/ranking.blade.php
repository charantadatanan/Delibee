<div>
    <header class="max-w-3xl">
        <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">BeeRank</h1>
        <p class="mt-3 text-zinc-600 dark:text-zinc-400">
            The monthly ranking scores businesses on how they performed over the last 30 days: how customers
            rated them, how many orders they completed, how often they delivered on time, and how quickly they
            replied. A new business that is doing well can climb here faster than an established one.
        </p>
    </header>

    <section class="mt-10">
        <div class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-display text-2xl font-semibold">Monthly ranking</h2>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $periodStart->format('F Y') }}</p>
        </div>

        @if ($published->isEmpty())
            <p class="mt-4 rounded-2xl border border-dashed border-comb bg-white/50 p-10 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-400">
                The ranking for this month has not been published yet.
            </p>
        @else
            <ol class="mt-4 overflow-hidden rounded-2xl border border-comb/90 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900/70">
                @foreach ($published as $snapshot)
                    <li class="flex items-center gap-4 border-b border-comb/70 p-4 last:border-b-0 dark:border-zinc-800">
                        <span class="clip-hex grid size-10 shrink-0 place-items-center font-display text-sm font-bold {{ $snapshot->rank <= 3 ? 'bg-honey text-ink' : 'bg-comb/70 text-honey-deep dark:bg-zinc-800 dark:text-honey' }}">
                            {{ $snapshot->rank }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('businesses.show', $snapshot->business->slug) }}"
                               class="font-display text-lg font-semibold transition-colors hover:text-honey-deep dark:hover:text-honey"
                               wire:navigate>
                                {{ $snapshot->business->name }}
                            </a>
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $snapshot->metrics['orders'] ?? 0 }} orders in the last 30 days,
                                {{ $snapshot->metrics['reviews'] ?? 0 }} verified ratings
                            </p>
                        </div>

                        <span class="font-display text-2xl font-semibold">{{ number_format((float) $snapshot->score, 1) }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <section class="mt-12">
        <h2 class="font-display text-2xl font-semibold">Rising bees</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            New businesses, less than {{ config('beerank.rising_bee_months') }} months old, that are already trading well.
        </p>

        @if ($rising->isEmpty())
            <p class="mt-4 rounded-2xl border border-dashed border-comb bg-white/50 p-10 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-400">
                No rising businesses right now.
            </p>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rising as $row)
                    <a href="{{ route('businesses.show', $row['business']->slug) }}"
                       class="group relative overflow-hidden rounded-2xl border border-honey/50 bg-white/70 p-5 transition-colors hover:border-honey-deep/50 dark:border-honey/25 dark:bg-zinc-900/70 dark:hover:border-honey/50"
                       wire:navigate>
                        <div class="absolute inset-0 bg-comb opacity-30 dark:opacity-10"></div>

                        <div class="relative">
                            <p class="text-xs font-semibold text-honey-deep dark:text-honey">Rising Bee</p>
                            <h3 class="mt-1 font-display text-xl font-semibold tracking-tight group-hover:text-honey-deep dark:group-hover:text-honey">
                                {{ $row['business']->name }}
                            </h3>
                            <p class="mt-3 font-display text-3xl font-semibold text-honey-deep dark:text-honey">
                                {{ number_format($row['result']->score, 1) }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $row['result']->orderCount }} orders in the last 30 days, trading since
                                {{ $row['business']->created_at?->format('F Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
