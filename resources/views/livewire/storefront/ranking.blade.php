<div>
    <h1 class="text-3xl font-bold tracking-tight">BeeRank</h1>
    <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">
        The monthly ranking scores businesses on how they performed over the last 30 days — how customers
        rated them, how many orders they completed, how often they delivered on time, and how quickly they
        replied. A new business that is doing well can climb here faster than an established one.
    </p>

    <section class="mt-10">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-xl font-semibold">Monthly Ranking</h2>
            <p class="text-sm text-zinc-500">
                {{ $periodStart->format('F Y') }}
            </p>
        </div>

        @if ($published->isEmpty())
            <p class="mt-4 rounded-xl border border-dashed border-zinc-300 p-10 text-center text-zinc-500 dark:border-zinc-700">
                The ranking for this month has not been published yet.
            </p>
        @else
            <ol class="mt-4 divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-800">
                @foreach ($published as $snapshot)
                    <li class="flex items-center gap-4 p-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-amber-100 text-sm font-bold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                            {{ $snapshot->rank }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('businesses.show', $snapshot->business->slug) }}"
                               class="font-medium hover:text-amber-600 dark:hover:text-amber-400"
                               wire:navigate>
                                {{ $snapshot->business->name }}
                            </a>
                            <p class="text-xs text-zinc-500">
                                {{ $snapshot->metrics['orders'] ?? 0 }} orders ·
                                {{ $snapshot->metrics['reviews'] ?? 0 }} verified ratings
                            </p>
                        </div>

                        <span class="text-lg font-semibold">{{ number_format((float) $snapshot->score, 1) }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <section class="mt-12">
        <h2 class="text-xl font-semibold">Rising Bees</h2>
        <p class="mt-1 text-sm text-zinc-500">
            New businesses (less than {{ config('beerank.rising_bee_months') }} months old) that are already
            trading well.
        </p>

        @if ($rising->isEmpty())
            <p class="mt-4 rounded-xl border border-dashed border-zinc-300 p-10 text-center text-zinc-500 dark:border-zinc-700">
                No rising businesses right now.
            </p>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rising as $row)
                    <a href="{{ route('businesses.show', $row['business']->slug) }}"
                       class="rounded-xl border border-amber-300 bg-white p-5 transition hover:shadow-md dark:border-amber-500/40 dark:bg-zinc-800"
                       wire:navigate>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Rising Bee</p>
                        <h3 class="mt-1 font-semibold">{{ $row['business']->name }}</h3>
                        <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {{ number_format($row['result']->score, 1) }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            {{ $row['result']->orderCount }} orders in the last 30 days ·
                            trading since {{ $row['business']->created_at?->format('F Y') }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
