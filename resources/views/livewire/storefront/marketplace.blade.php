<div>
    <div class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-bold tracking-tight">Hive Marketplace</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">
            Everything on deliBEE, from many sellers in one place. Businesses are ranked on how they are
            performing right now, not on how long they have been around.
        </p>
    </div>

    <div class="mb-6 grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800 md:grid-cols-[2fr_1fr_1fr_1fr_1fr]">
        <label class="block">
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Search</span>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="Search products…"
                   class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-900">
        </label>

        <label class="block">
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Category</span>
            <select wire:model.live="category"
                    class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-900">
                <option value="">All categories</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->slug }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Min price</span>
            <input type="number" min="0" step="1" wire:model.live.debounce.500ms="minPrice"
                   class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-900">
        </label>

        <label class="block">
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Max price</span>
            <input type="number" min="0" step="1" wire:model.live.debounce.500ms="maxPrice"
                   class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-900">
        </label>

        <label class="block">
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Sort by</span>
            <select wire:model.live="sort"
                    class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-900">
                @foreach (\App\Livewire\Storefront\Marketplace::SORTS as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </label>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <p class="text-lg font-medium">No products matched.</p>
            <p class="mt-1 text-sm text-zinc-500">Try a different search or clear the filters.</p>
            <button type="button"
                    wire:click="clearFilters"
                    class="mt-4 rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium dark:border-zinc-600">
                Clear filters
            </button>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</div>
