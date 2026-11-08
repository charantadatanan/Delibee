@php
    $categories = \App\Models\Category::query()
        ->active()
        ->withCount(['products' => fn ($query) => $query->active()])
        ->orderBy('position')
        ->get();
@endphp

<x-storefront.layout title="Categories">
    <h1 class="text-3xl font-bold tracking-tight">Flower Fields</h1>
    <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">
        Browse the marketplace by category.
    </p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category->slug) }}"
               class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-400 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800"
               wire:navigate>
                <h2 class="font-semibold">{{ $category->name }}</h2>
                @if ($category->description)
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $category->description }}</p>
                @endif
                <p class="mt-3 text-xs uppercase tracking-wide text-zinc-500">
                    {{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}
                </p>
            </a>
        @endforeach
    </div>
</x-storefront.layout>
