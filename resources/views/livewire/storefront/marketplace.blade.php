<div>
    {{-- Mobile home --}}
    <div class="lg:hidden">
        <h1 class="sr-only">Marketplace</h1>

        <div x-data="{ splash: false }"
             x-init="(() => { try { if (window.matchMedia('(max-width: 1023px)').matches && !sessionStorage.getItem('delibee-splash') && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) { splash = true; sessionStorage.setItem('delibee-splash', '1'); setTimeout(() => splash = false, 1350); } } catch (e) {} })()"
             x-show="splash"
             x-cloak
             x-transition.opacity.duration.400ms
             @click="splash = false"
             class="fixed inset-0 z-[60] grid cursor-pointer place-items-center bg-paper dark:bg-zinc-950"
             aria-hidden="true">
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/mobile/bee-welcome.png') }}"
                     alt=""
                     width="256"
                     height="256"
                     class="mobile-splash-bee size-64 object-contain">
                <p class="-mt-2 font-display text-sm text-honey-deep dark:text-honey">shop. support. grow together.</p>
            </div>
        </div>

        @php($activeFilters = (int) ($category !== '') + (int) ($minPrice !== '') + (int) ($maxPrice !== '') + (int) ($sort !== ''))

        <div class="mobile-reveal mt-3" x-data="{ filters: false }">
            <div class="relative">
                <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-zinc-500 dark:text-zinc-400" />
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search products or shops"
                       aria-label="Search products and businesses"
                       class="min-h-11 w-full rounded-lg border border-ink/70 bg-paper py-3 ps-11 pe-14 text-base placeholder:text-zinc-500 focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:placeholder:text-zinc-400 dark:focus:ring-honey">
                <button type="button"
                        @click="filters = !filters"
                        :aria-expanded="filters.toString()"
                        aria-expanded="false"
                        aria-controls="mobile-filters"
                        aria-label="Filters and sorting"
                        class="absolute end-1.5 top-1/2 grid size-11 -translate-y-1/2 place-items-center rounded-lg text-ink transition hover:bg-comb/50 dark:text-zinc-100">
                    <flux:icon name="funnel" class="size-5" />

                    @if ($activeFilters > 0)
                        <span class="absolute -end-1 -top-1 grid min-w-4 place-items-center rounded-full bg-ink px-1 text-[9px] font-semibold text-paper dark:bg-zinc-100 dark:text-zinc-900">
                            {{ $activeFilters }}
                        </span>
                    @endif
                </button>
            </div>

            <div wire:loading.delay.short
                 wire:target="search,category,minPrice,maxPrice,sort,clearFilters"
                 class="mt-1 h-1 overflow-hidden rounded-full bg-honey/20">
                <div class="h-full w-1/2 animate-pulse rounded-full bg-honey motion-reduce:animate-none"></div>
            </div>

            <div id="mobile-filters"
                 x-show="filters"
                 x-cloak
                 x-transition.origin.top.right
                 class="mobile-filters-panel mt-2 origin-top-right rounded-2xl border border-ink/30 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900/70">
                <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" role="group" aria-label="Filter by category">
                    <button type="button"
                            wire:click="$set('category', '')"
                            aria-pressed="{{ $category === '' ? 'true' : 'false' }}"
                            class="min-h-9 whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium transition active:scale-95 {{ $category === '' ? 'bg-ink text-paper dark:bg-zinc-100 dark:text-zinc-900' : 'border border-ink/60 bg-white text-zinc-600 hover:border-honey-deep/40 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                        All
                    </button>

                    @foreach ($categories as $option)
                        <button type="button"
                                wire:click="$set('category', '{{ $option->slug }}')"
                                aria-pressed="{{ $category === $option->slug ? 'true' : 'false' }}"
                                class="min-h-9 whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium transition active:scale-95 {{ $category === $option->slug ? 'bg-ink text-paper dark:bg-zinc-100 dark:text-zinc-900' : 'border border-ink/60 bg-white text-zinc-600 hover:border-honey-deep/40 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                            {{ $option->name }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Min price</span>
                        <input type="number" min="0" step="1" wire:model.live.debounce.500ms="minPrice"
                               class="mt-1 min-h-11 w-full rounded-lg border border-ink/50 bg-white px-3 py-2.5 text-base focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-honey">
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Max price</span>
                        <input type="number" min="0" step="1" wire:model.live.debounce.500ms="maxPrice"
                               class="mt-1 min-h-11 w-full rounded-lg border border-ink/50 bg-white px-3 py-2.5 text-base focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-honey">
                    </label>

                    <label class="col-span-2 block">
                        <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Sort by</span>
                        <select wire:model.live="sort"
                                class="mt-1 min-h-11 w-full rounded-lg border border-ink/50 bg-white px-3 py-2.5 text-base focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-honey">
                            @foreach (\App\Livewire\Storefront\Marketplace::SORTS as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="button"
                            wire:click="clearFilters"
                            class="col-span-2 min-h-11 rounded-lg border border-ink/60 px-3 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-honey-deep/40 hover:text-ink dark:border-zinc-700 dark:text-zinc-300 dark:hover:text-ink">
                        Clear filters
                    </button>

                    <button type="button"
                            @click="filters = false"
                            class="col-span-2 min-h-11 rounded-lg bg-ink px-3 py-2.5 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink">
                        Show {{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }}
                    </button>
                </div>
            </div>
        </div>

        <p class="sr-only" aria-live="polite">
            {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} found
        </p>

        @php($featured = collect($products->items())->take(5))

        @if ($featured->isNotEmpty())
            <section class="relative mt-4"
                     aria-label="Top products"
                     x-data="{ slide(direction) { const track = this.$refs.track; const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'; track.scrollBy({ left: direction * track.clientWidth, behavior }); } }">
                <h2 class="sr-only">Top Products</h2>

                <div class="pointer-events-none absolute end-3 top-3 z-10 flex gap-1.5">
                    <button type="button"
                            @click="slide(-1)"
                            aria-label="Previous top products"
                            class="mobile-carousel-button pointer-events-auto grid size-11 place-items-center rounded-full bg-paper/90 text-ink shadow-sm transition hover:bg-paper active:scale-95">
                        <span aria-hidden="true">←</span>
                    </button>
                    <button type="button"
                            @click="slide(1)"
                            aria-label="Next top products"
                            class="mobile-carousel-button pointer-events-auto grid size-11 place-items-center rounded-full bg-paper/90 text-ink shadow-sm transition hover:bg-paper active:scale-95">
                        <span aria-hidden="true">→</span>
                    </button>
                </div>

                <div x-ref="track"
                     class="mobile-carousel no-scrollbar flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth motion-reduce:scroll-auto">
                    @foreach ($featured as $product)
                        @php($image = $product->images->first())

                        <a href="{{ route('products.show', [$product->business->slug, $product->slug]) }}"
                           wire:key="top-{{ $product->id }}"
                           class="group relative block aspect-[339/185] w-full shrink-0 snap-start overflow-hidden rounded-[20px] bg-hero transition-transform duration-200 ease-out hover:-translate-y-0.5 active:scale-[0.99] motion-reduce:transition-none dark:bg-zinc-800"
                           wire:navigate>
                            <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-10"></div>

                            @if ($image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}"
                                     alt="{{ $image->alt_text ?? $product->name }}"
                                     loading="lazy"
                                     decoding="async"
                                     class="relative size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                            @else
                                <span class="clip-hex absolute left-1/2 top-1/2 grid size-16 -translate-x-1/2 -translate-y-1/2 place-items-center bg-white/85 font-display text-2xl font-semibold text-honey-deep shadow-sm dark:bg-zinc-900/80 dark:text-honey">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                </span>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/45 to-ink/10"></div>

                            <span class="absolute left-3 top-3 rounded-full bg-honey px-3 py-1 text-[11px] font-semibold text-ink">
                                Top Products
                            </span>

                            <div class="absolute inset-x-3 bottom-3 flex items-end justify-between gap-3 text-paper">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-paper">
                                        <span class="grid size-5 place-items-center rounded-full bg-paper text-[9px] font-bold text-honey-deep">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->business->name, 0, 1)) }}
                                        </span>
                                        {{ $product->business->name }}
                                    </p>
                                    <p class="mt-1 truncate font-display text-lg font-semibold">{{ $product->name }}</p>
                                </div>

                                <span class="shrink-0 rounded-full bg-honey px-3 py-1.5 text-xs font-semibold text-ink">View</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($featuredBusinesses->isNotEmpty())
            <section class="mobile-reveal mobile-reveal-delay-2 mt-6" aria-label="Featured shops">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-xl font-semibold">Featured Shops</h2>
                    <a href="{{ route('categories.index') }}"
                       class="text-xs font-semibold text-honey-deep dark:text-honey"
                       wire:navigate>
                        Browse categories →
                    </a>
                </div>

                <div class="no-scrollbar mt-3 flex gap-3 overflow-x-auto pb-1">
                    @foreach ($featuredBusinesses as $business)
                        <a href="{{ route('businesses.show', $business) }}"
                           wire:key="shop-{{ $business->id }}"
                           style="--shop-index: {{ $loop->index }}"
                           class="mobile-shop-reveal group flex w-16 shrink-0 flex-col items-center gap-1.5 text-center transition-transform duration-200 hover:-translate-y-0.5 active:scale-95 motion-reduce:transition-none"
                           wire:navigate>
                            @php($shopImage = $business->products->isNotEmpty() ? $business->products[$business->id % $business->products->count()]->images->first() : null)

                            <span class="mobile-shop-logo grid size-[50px] place-items-center overflow-hidden rounded-full border border-ink/40 bg-white shadow-sm transition group-hover:scale-[1.03] dark:border-zinc-700 dark:bg-zinc-900">
                                @if ($business->logo_path || $shopImage)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($business->logo_path ?? $shopImage->path) }}"
                                         alt=""
                                         loading="lazy"
                                         decoding="async"
                                         class="size-full object-cover">
                                @else
                                    <span class="font-display text-lg font-bold text-honey-deep dark:text-honey">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                                    </span>
                                @endif
                            </span>
                            <span class="line-clamp-2 w-full text-[11px] font-semibold leading-tight">{{ $business->name }}</span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $business->products_count }} {{ \Illuminate\Support\Str::plural('product', $business->products_count) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mobile-reveal mobile-reveal-delay-3 mt-6" aria-label="Marketplace products">
            <div class="flex items-baseline justify-between">
                <h2 class="font-display text-xl font-semibold">The marketplace</h2>
            </div>

            @if ($products->isEmpty())
                <div class="mt-3 rounded-2xl border border-dashed border-ink/40 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                    <p class="font-display text-lg font-semibold">Nothing in this part of the hive.</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Try another search or clear the filters.</p>
                    <button type="button"
                            wire:click="clearFilters"
                            class="mt-4 min-h-11 rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey">
                        Clear filters
                    </button>
                </div>
            @else
                <div class="mt-3 grid grid-cols-2 gap-3 min-[480px]:grid-cols-3 min-[480px]:gap-4">
                    @foreach ($products as $product)
                        @php($image = $product->images->first())

                        <a href="{{ route('products.show', [$product->business->slug, $product->slug]) }}"
                           wire:key="trend-{{ $product->id }}"
                           style="--card-index: {{ min($loop->index, 8) }}"
                           class="mobile-card-reveal group overflow-hidden rounded-xl border border-ink/60 bg-white transition duration-200 hover:-translate-y-0.5 hover:border-ink active:scale-[0.99] motion-reduce:transition-none dark:border-zinc-800 dark:bg-zinc-900/70"
                           wire:navigate>
                            <div class="relative aspect-square overflow-hidden bg-hero dark:bg-zinc-800">
                                <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

                                @if ($image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}"
                                         alt="{{ $image->alt_text ?? $product->name }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="relative size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                                @else
                                    <span class="clip-hex absolute left-1/2 top-1/2 grid size-10 -translate-x-1/2 -translate-y-1/2 place-items-center bg-white/85 font-display text-lg font-semibold text-honey-deep shadow-sm dark:bg-zinc-900/80 dark:text-honey">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>

                            <p class="truncate bg-honey px-2.5 py-1.5 text-[10px] font-semibold leading-tight text-ink">
                                {{ $product->category?->name ?? 'Marketplace' }}
                            </p>

                            <div class="p-2.5">
                                <p class="line-clamp-2 text-[13px] font-medium leading-snug">{{ $product->name }}</p>
                                <div class="mt-1.5 flex items-baseline justify-between gap-1.5">
                                    <p class="truncate text-xs font-semibold">{{ $product->formattedPrice() }}</p>
                                    <p class="shrink-0 text-[10px] text-zinc-500 dark:text-zinc-400">{{ $product->stock }} left</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-5">{{ $products->links(data: ['scrollTo' => false]) }}</div>
            @endif
        </section>

        @if ($products->isNotEmpty() && $products->currentPage() === $products->lastPage())
            <div class="mobile-reveal mt-8 flex flex-col items-center gap-2 pb-4 text-center">
                <span class="clip-hex grid size-10 place-items-center bg-honey font-display text-lg font-bold text-ink">d</span>
                <p class="font-display text-lg font-semibold">Good sellers rise. The hive decides.</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">You have reached the end of the hive.</p>
            </div>
        @endif
    </div>

    {{-- Desktop marketplace --}}
    <div class="hidden lg:block">
        @php($spotlight = $featuredBusinesses->firstWhere('slug', 'sunrise-orchard'))
        @php($spotlightImage = $spotlight?->products->firstWhere('name', 'Golden Mangoes')?->images->first() ?? $spotlight?->products->first()?->images->first())

        <section class="relative overflow-hidden rounded-[32px] border border-ink/70 bg-hero dark:border-zinc-800 dark:bg-zinc-900">
            <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

            <div class="relative grid gap-8 p-6 sm:p-10 lg:grid-cols-[1.18fr_0.82fr] lg:items-center">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-ink/40 bg-paper/80 px-3 py-1 text-xs font-semibold text-ink">
                        <span class="size-1.5 rounded-full bg-leaf" aria-hidden="true"></span>
                        Your local food marketplace, ranked every month
                    </p>

                    <h1 class="mt-5 max-w-xl font-display text-4xl font-semibold leading-[1.02] tracking-tight sm:text-6xl">
                        Good sellers rise.<br class="hidden sm:block">
                        The hive decides.
                    </h1>

                    <p class="mt-5 max-w-lg text-base leading-relaxed text-zinc-700 dark:text-zinc-300">
                        Every business is ranked on what it is doing now — not on how long it has been around.
                        One basket across many local sellers, delivered from the hive.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#marketplace-products" class="ui-btn">
                            Shop the marketplace
                            <flux:icon name="arrow-down" class="size-4" />
                        </a>
                        <a href="{{ route('ranking') }}" class="ui-btn ui-btn-ghost" wire:navigate>
                            See BeeRank
                        </a>
                    </div>

                    <ul class="mt-7 flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <li class="flex items-center gap-2">
                            <span class="size-1.5 rounded-full bg-honey-deep dark:bg-honey"></span>A rolling 30-day window
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="size-1.5 rounded-full bg-honey-deep dark:bg-honey"></span>Verified ratings only
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="size-1.5 rounded-full bg-honey-deep dark:bg-honey"></span>One basket, many sellers
                        </li>
                    </ul>
                </div>

                <div class="relative">
                    <div class="ui-card rotate-[-1.2deg] bg-paper p-5 dark:bg-zinc-950">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-honey-deep dark:text-honey">This month</p>
                                <h2 class="mt-1 font-display text-2xl font-semibold">Top of the hive</h2>
                            </div>
                            <span class="grid size-12 place-items-center rounded-full border border-ink/60 bg-honey font-display text-lg font-bold">1</span>
                        </div>

                        <a href="{{ route('businesses.show', $spotlight?->slug ?? 'sunrise-orchard') }}"
                           class="mt-4 flex items-center gap-3 rounded-xl border border-ink/40 bg-white/80 p-3 transition hover:border-ink hover:bg-white dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500"
                           wire:navigate>
                            <span class="clip-hex grid size-10 place-items-center overflow-hidden bg-honey font-display font-bold">
                                @if ($spotlightImage)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($spotlightImage->path) }}"
                                         alt=""
                                         decoding="async"
                                         class="size-full object-cover">
                                @else
                                    S
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold">Sunrise Orchard</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Fresh fruit & cold-pressed juice</p>
                            </div>
                            <span class="rounded-full bg-leaf/10 px-2.5 py-1 text-xs font-semibold text-leaf dark:text-emerald-300">Top rated</span>
                        </a>

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px]">
                            <div class="rounded-xl border border-ink/20 bg-white/60 p-2 dark:border-zinc-700 dark:bg-zinc-900">
                                <p class="font-display text-lg font-semibold">30</p>
                                <p class="text-zinc-500 dark:text-zinc-400">day window</p>
                            </div>
                            <div class="rounded-xl border border-ink/20 bg-white/60 p-2 dark:border-zinc-700 dark:bg-zinc-900">
                                <p class="font-display text-lg font-semibold">5.0</p>
                                <p class="text-zinc-500 dark:text-zinc-400">verified ★</p>
                            </div>
                            <div class="rounded-xl border border-ink/20 bg-white/60 p-2 dark:border-zinc-700 dark:bg-zinc-900">
                                <p class="font-display text-lg font-semibold">5+</p>
                                <p class="text-zinc-500 dark:text-zinc-400">orders</p>
                            </div>
                        </div>

                        <a href="{{ route('ranking') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                            How the hive ranks
                            <flux:icon name="arrow-right" class="size-4" />
                        </a>
                    </div>

                </div>
            </div>
        </section>

        @if ($featuredBusinesses->isNotEmpty())
            <section class="mt-10" aria-label="Featured shops">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Local favourites</p>
                        <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">Featured Shops</h2>
                    </div>
                    <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                        Browse categories →
                    </a>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featuredBusinesses->take(4) as $business)
                        <a href="{{ route('businesses.show', $business) }}"
                           class="group ui-card flex items-center gap-4 bg-white/75 p-4 transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900"
                           wire:navigate>
                            @php($shopImage = $business->products->isNotEmpty() ? $business->products[$business->id % $business->products->count()]->images->first() : null)

                            <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-full border border-ink/50 bg-hero font-display text-xl font-bold text-honey-deep dark:bg-zinc-950 dark:text-honey">
                                @if ($business->logo_path || $shopImage)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($business->logo_path ?? $shopImage->path) }}"
                                         alt=""
                                         loading="lazy"
                                         decoding="async"
                                         class="size-full object-cover">
                                @else
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-display text-lg font-semibold">{{ $business->name }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $business->products_count }} {{ \Illuminate\Support\Str::plural('product', $business->products_count) }} in the hive
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div id="marketplace-products" class="mt-10 scroll-mt-24 rounded-3xl border border-ink/20 bg-white/70 p-4 shadow-[0_18px_50px_-40px_rgba(30,30,30,0.7)] dark:border-zinc-800 dark:bg-zinc-900/70">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 px-1">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Browse all</p>
                    <h2 class="mt-1 font-display text-2xl font-semibold">The marketplace</h2>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}</p>
            </div>

            <div class="grid gap-3 md:grid-cols-[2fr_1fr_1fr_1fr_1fr]">
                <label class="block">
                    <span class="ui-label">Search</span>
                    <input type="search"
                           wire:model.live.debounce.400ms="search"
                           placeholder="Search products"
                           class="ui-field mt-1.5">
                </label>

                <label class="block">
                    <span class="ui-label">Category</span>
                    <select wire:model.live="category" class="ui-field mt-1.5">
                        <option value="">All categories</option>
                        @foreach ($categories as $option)
                            <option value="{{ $option->slug }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="ui-label">Min price</span>
                    <input type="number" min="0" step="1" wire:model.live.debounce.500ms="minPrice" class="ui-field mt-1.5">
                </label>

                <label class="block">
                    <span class="ui-label">Max price</span>
                    <input type="number" min="0" step="1" wire:model.live.debounce.500ms="maxPrice" class="ui-field mt-1.5">
                </label>

                <label class="block">
                    <span class="ui-label">Sort by</span>
                    <select wire:model.live="sort" class="ui-field mt-1.5">
                        @foreach (\App\Livewire\Storefront\Marketplace::SORTS as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="mt-6 rounded-3xl border border-dashed border-ink/40 bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <p class="font-display text-xl font-semibold">Nothing in this part of the hive.</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Try a different search, or clear the filters to see everything.</p>
                <button type="button"
                        wire:click="clearFilters"
                        class="ui-btn mt-5">
                    Clear filters
                </button>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" wire:key="desktop-product-{{ $product->id }}" />
                @endforeach
            </div>

            <div class="mt-8">{{ $products->links(data: ['scrollTo' => false]) }}</div>
        @endif
    </div>
</div>
