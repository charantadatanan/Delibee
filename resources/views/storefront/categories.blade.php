@php
    $categories = \App\Models\Category::query()
        ->active()
        ->withCount(['products' => fn ($query) => $query->active()])
        ->orderBy('position')
        ->get();
@endphp

<x-storefront.layout title="Categories">
    <h1 class="font-display text-4xl font-semibold tracking-tight">Flower Fields</h1>
    <p class="mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400">
        Browse the marketplace by category.
    </p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category->slug) }}"
               class="group rounded-2xl border border-comb/90 bg-white/70 p-5 transition-colors hover:border-honey-deep/40 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:border-honey/40"
               wire:navigate>
                <h2 class="font-display text-lg font-semibold transition-colors group-hover:text-honey-deep dark:group-hover:text-honey">
                    {{ $category->name }}
                </h2>
                @if ($category->description)
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $category->description }}</p>
                @endif
                <p class="mt-3 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    {{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}
                </p>
            </a>
        @endforeach
    </div>
</x-storefront.layout>
