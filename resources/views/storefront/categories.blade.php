@php
    $categories = \App\Models\Category::query()
        ->active()
        ->withCount(['products' => fn ($query) => $query->active()])
        ->orderBy('position')
        ->get();
@endphp

<x-storefront.layout title="Categories">
    <div class="flex items-center gap-3 lg:hidden">
        <a href="{{ route('marketplace') }}" class="ui-back size-9" aria-label="Back to marketplace" wire:navigate>
            <flux:icon name="chevron-left" class="size-5" />
        </a>
        <h1 class="font-display text-2xl font-semibold">Flower Fields</h1>
    </div>

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Browse the hive</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Flower Fields</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            Browse the marketplace by category. Every shelf is stocked by local businesses.
        </p>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category->slug) }}"
               class="group ui-card flex items-start gap-4 bg-paper p-4 transition duration-200 hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900"
               wire:navigate>
                <span class="grid size-12 shrink-0 place-items-center rounded-full border border-ink/50 bg-honey font-display text-lg font-bold text-ink">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 1)) }}
                </span>

                <span class="min-w-0 flex-1">
                    <span class="flex items-start justify-between gap-2">
                        <span class="font-display text-lg font-semibold leading-tight">{{ $category->name }}</span>
                        <flux:icon name="arrow-up-right" class="mt-1 size-4 shrink-0 text-zinc-400 transition group-hover:text-honey-deep dark:group-hover:text-honey" />
                    </span>

                    @if ($category->description)
                        <span class="mt-1 line-clamp-2 block text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $category->description }}</span>
                    @endif

                    <span class="mt-2 inline-flex rounded-full border border-ink/20 bg-white/60 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400">
                        {{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}
                    </span>
                </span>
            </a>
        @endforeach
    </div>
</x-storefront.layout>
