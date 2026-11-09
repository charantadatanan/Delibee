@php
    $products = $category->products()
        ->active()
        ->with(['business', 'category', 'images'])
        ->paginate(24);
@endphp

<x-storefront.layout :title="$category->name">
    <nav aria-label="Breadcrumb" class="text-sm text-zinc-600 dark:text-zinc-400">
        <a href="{{ route('categories.index') }}" class="transition hover:text-honey-deep dark:hover:text-honey" wire:navigate>Categories</a>
        <span class="mx-1">/</span>
        <span>{{ $category->name }}</span>
    </nav>

    <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight">{{ $category->name }}</h1>
    @if ($category->description)
        <p class="mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $category->description }}</p>
    @endif

    @if ($products->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-400">
            Nothing in this category yet.
        </p>
    @else
        <h2 class="sr-only">Products in {{ $category->name }}</h2>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</x-storefront.layout>
