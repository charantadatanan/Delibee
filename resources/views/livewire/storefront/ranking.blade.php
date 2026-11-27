@php
    $topThree = $published->take(3);
    $podium = collect([2, 1, 3])->map(fn (int $position) => $topThree->firstWhere('rank', $position))->filter();
    $rest = $published->slice(3);
@endphp

<div>
    <header class="relative -mx-4 overflow-hidden bg-hero px-4 pb-10 pt-6 dark:bg-zinc-900 lg:mx-0 lg:rounded-[32px] lg:px-10 lg:pb-12 lg:pt-10">
        <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

        <div class="relative mx-auto max-w-3xl text-center">
            <p class="inline-flex items-center gap-2 rounded-full border border-ink/40 bg-paper/80 px-3 py-1 text-[11px] font-semibold">
                <span class="size-1.5 rounded-full bg-leaf" aria-hidden="true"></span>
                {{ $periodStart->format('F Y') }} leaderboard
            </p>
            <h1 class="mt-3 font-display text-4xl font-semibold tracking-tight sm:text-5xl">BeeRank</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                The monthly ranking scores businesses on how they performed over the last 30 days: verified ratings,
                completed orders, on-time delivery and reply speed. A new business doing good work can climb faster
                than an established one.
            </p>
        </div>
    </header>

    @if ($published->isEmpty())
        <div class="mt-6">
            <x-ui.empty-state
                title="The ranking is still being counted"
                description="This month's leaderboard has not been published yet. Check back after the next hive count."
                icon="trophy" />
        </div>
    @else
        <section class="mx-auto mt-6 max-w-3xl" aria-label="Top three businesses">
            <div class="grid grid-cols-3 items-end gap-2 sm:gap-4">
                @foreach ($podium as $snapshot)
                    @php($first = $snapshot->rank === 1)
                    <a href="{{ route('businesses.show', $snapshot->business->slug) }}"
                       class="group flex flex-col items-center text-center {{ $first ? 'order-2' : ($snapshot->rank === 2 ? 'order-1' : 'order-3') }}"
                       wire:navigate>
                        <span class="relative grid place-items-center overflow-hidden rounded-full border-2 border-ink bg-white font-display font-bold text-honey-deep shadow-[0_4px_0_0_var(--color-ink)] transition group-hover:-translate-y-1 dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey {{ $first ? 'size-24 text-3xl sm:size-28' : 'size-16 text-xl' }}">
                            @if ($snapshot->business->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($snapshot->business->logo_path) }}" alt="" class="size-full object-cover">
                            @else
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($snapshot->business->name, 0, 1)) }}
                            @endif
                            <span class="absolute -top-0 grid size-7 place-items-center rounded-full border border-ink bg-honey font-display text-xs font-bold text-ink">{{ $snapshot->rank }}</span>
                        </span>

                        <p class="mt-2 line-clamp-2 w-full text-xs font-semibold leading-tight sm:text-sm">{{ $snapshot->business->name }}</p>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400">
                            {{ number_format((int) ($snapshot->metrics['orders'] ?? 0)) }} sales
                        </p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="mt-6" aria-label="Monthly ranking">
            @if ($rest->isNotEmpty())
                <ol class="space-y-2">
                    @foreach ($rest as $snapshot)
                        <li>
                            <a href="{{ route('businesses.show', $snapshot->business->slug) }}"
                               class="ui-card flex items-center gap-3 bg-paper p-3 transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900"
                               wire:navigate>
                                <span class="grid size-9 shrink-0 place-items-center rounded-full border border-ink/30 bg-white font-display text-sm font-bold text-ink dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    {{ $snapshot->rank }}
                                </span>

                                <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-full border border-ink/40 bg-hero font-display text-sm font-semibold text-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey">
                                    @if ($snapshot->business->logo_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($snapshot->business->logo_path) }}" alt="" class="size-full object-cover">
                                    @else
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($snapshot->business->name, 0, 1)) }}
                                    @endif
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-display text-base font-semibold">{{ $snapshot->business->name }}</span>
                                    <span class="block text-[10px] text-zinc-500 dark:text-zinc-400">
                                        {{ $snapshot->metrics['orders'] ?? 0 }} orders · {{ $snapshot->metrics['reviews'] ?? 0 }} verified ratings
                                    </span>
                                </span>

                                <span class="ui-price shrink-0 text-base">{{ number_format((float) $snapshot->score, 1) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @endif

    <section class="mt-8">
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">New blood</p>
                <h2 class="mt-1 font-display text-2xl font-semibold">Rising bees</h2>
            </div>
            <span class="ui-chip">Under {{ config('beerank.rising_bee_months') }} months old</span>
        </div>

        @if ($rising->isEmpty())
            <p class="mt-3 rounded-2xl border border-dashed border-ink/25 p-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                No rising businesses right now.
            </p>
        @else
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rising as $row)
                    <a href="{{ route('businesses.show', $row['business']->slug) }}"
                       class="group ui-card-honey p-4 transition hover:-translate-y-0.5"
                       wire:navigate>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-700">Rising Bee</p>
                            <span class="grid size-7 place-items-center rounded-full border border-ink/40 bg-honey text-[10px] font-bold">↑</span>
                        </div>
                        <h3 class="mt-2 font-display text-xl font-semibold tracking-tight">{{ $row['business']->name }}</h3>
                        <p class="mt-2 font-display text-3xl font-semibold">{{ number_format($row['result']->score, 1) }}</p>
                        <p class="mt-1 text-[10px] text-zinc-600">
                            {{ $row['result']->orderCount }} orders / 30d · since {{ $row['business']->created_at?->format('M Y') }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
