<div>
    <section class="relative overflow-hidden rounded-3xl border border-comb bg-[#FFF4C5] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-10"></div>

        <div class="relative grid gap-8 p-6 sm:p-10 lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
            <div>
                <h1 class="max-w-xl font-display text-4xl font-semibold leading-[1.05] tracking-tight sm:text-5xl">
                    Good sellers rise.<br class="hidden sm:block">
                    The hive decides.
                </h1>

                <p class="mt-4 max-w-xl text-zinc-600 dark:text-zinc-400">
                    Everything on deliBEE, from many sellers in one place. Businesses are ranked on how they
                    are performing right now, not on how long they have been around.
                </p>

                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <li class="flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-honey"></span>A rolling 30-day window
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-honey"></span>Verified ratings only
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-honey"></span>One basket, many sellers
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-comb/90 bg-paper/80 p-5 dark:border-zinc-700 dark:bg-zinc-950/60">
                <h2 class="font-display text-lg font-semibold">How the hive ranks</h2>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex gap-3">
                        <dt class="clip-hex grid size-8 shrink-0 place-items-center bg-honey text-xs font-bold text-ink">30</dt>
                        <dd>
                            <span class="font-medium">Thirty days of trading.</span>
                            <span class="text-zinc-500 dark:text-zinc-400">Recent form beats lifetime totals.</span>
                        </dd>
                    </div>

                    <div class="flex gap-3">
                        <dt class="clip-hex grid size-8 shrink-0 place-items-center bg-honey text-xs font-bold text-ink">★</dt>
                        <dd>
                            <span class="font-medium">Verified ratings only.</span>
                            <span class="text-zinc-500 dark:text-zinc-400">Reviews must come from a delivered order.</span>
                        </dd>
                    </div>

                    <div class="flex gap-3">
                        <dt class="clip-hex grid size-8 shrink-0 place-items-center bg-honey text-xs font-bold text-ink">5</dt>
                        <dd>
                            <span class="font-medium">Five orders to qualify.</span>
                            <span class="text-zinc-500 dark:text-zinc-400">No score from a single lucky sale.</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <div class="mt-8 grid gap-3 rounded-2xl border border-comb/90 bg-white/70 p-4 dark:border-zinc-800 dark:bg-zinc-900 md:grid-cols-[2fr_1fr_1fr_1fr_1fr]">
        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Search</span>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="Search products"
                   class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm placeholder:text-zinc-400 focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950 dark:placeholder:text-zinc-500">
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Category</span>
            <select wire:model.live="category"
                    class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                <option value="">All categories</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->slug }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Min price</span>
            <input type="number" min="0" step="1" wire:model.live.debounce.500ms="minPrice"
                   class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Max price</span>
            <input type="number" min="0" step="1" wire:model.live.debounce.500ms="maxPrice"
                   class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Sort by</span>
            <select wire:model.live="sort"
                    class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                @foreach (\App\Livewire\Storefront\Marketplace::SORTS as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </label>
    </div>

    @if ($products->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">Nothing in this part of the hive.</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Try a different search, or clear the filters to see everything.</p>
            <button type="button"
                    wire:click="clearFilters"
                    class="mt-5 rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey">
                Clear filters
            </button>
        </div>
    @else
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</div>
