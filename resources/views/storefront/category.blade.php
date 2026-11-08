@php
    $products = $category->products()
        ->active()
        ->with(['business', 'category', 'images'])
        ->paginate(24);
@endphp

<x-storefront.layout :title="$category->name">
    <nav class="text-sm text-zinc-500">
        <a href="{{ route('categories.index') }}" class="hover:text-amber-600" wire:navigate>Categories</a>
        <span class="mx-1">/</span>
        <span>{{ $category->name }}</span>
    </nav>

    <h1 class="mt-2 text-3xl font-bold tracking-tight">{{ $category->name }}</h1>
    @if ($category->description)
        <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $category->description }}</p>
    @endif

    @if ($products->isEmpty())
        <p class="mt-8 rounded-xl border border-dashed border-zinc-300 p-12 text-center text-zinc-500 dark:border-zinc-700">
            Nothing in this category yet.
        </p>
    @else
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</x-storefront.layout>
