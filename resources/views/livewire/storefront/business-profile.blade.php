<div x-data="{ tab: 'products' }">
    {{-- Store hero, matching the blue storefront banner in the Figma profile. --}}
    <section class="relative -mx-4 overflow-hidden bg-[#2B2BD6] text-white lg:mx-0 lg:rounded-[32px]">
        <div class="absolute inset-0 bg-comb opacity-15"></div>

        <div class="relative px-4 pb-24 pt-4 lg:px-8 lg:pb-16 lg:pt-8">
            <div class="flex items-center justify-between lg:hidden">
                <a href="{{ route('marketplace') }}" class="ui-back size-9" aria-label="Back to marketplace" wire:navigate>
                    <flux:icon name="chevron-left" class="size-5" />
                </a>

                <button type="button" wire:click="startChat" class="grid size-9 place-items-center rounded-full border border-white/50 bg-white/10 text-white backdrop-blur" aria-label="Message {{ $business->name }}">
                    <flux:icon name="chat-bubble-left-right" class="size-4" />
                </button>
            </div>

            <p class="mt-7 text-center font-display text-2xl font-semibold leading-tight lg:mt-0 lg:text-4xl">
                {{ \Illuminate\Support\Str::upper($business->name) }}
            </p>
            <p class="mt-1 text-center text-[11px] font-medium uppercase tracking-[0.18em] text-white/65">
                {{ $business->isRising() ? 'Rising local business' : 'Verified local business' }}
            </p>
        </div>
    </section>

    {{-- Store identity card overlapping the banner. --}}
    <section class="relative z-10 -mt-16 lg:-mt-12">
        <div class="ui-card bg-paper p-4 shadow-[0_18px_40px_-30px_rgba(30,30,30,0.8)] dark:bg-zinc-900 lg:p-6">
            <div class="flex items-start gap-4">
                <span class="grid size-[72px] shrink-0 place-items-center overflow-hidden rounded-full border-2 border-ink bg-white font-display text-3xl font-bold text-honey-deep shadow-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey lg:size-24">
                    @if ($business->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($business->logo_path) }}" alt="" class="size-full object-cover">
                    @else
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-display text-xl font-semibold leading-tight lg:text-3xl">{{ $business->name }}</h1>
                        @if ($business->isRising())
                            <span class="rounded-full border border-leaf/40 bg-leaf/10 px-2.5 py-0.5 text-[10px] font-semibold text-leaf dark:text-emerald-300">
                                Rising Bee
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                        {{ $business->description ? \Illuminate\Support\Str::limit($business->description, 80) : 'A local business in the deliBEE hive.' }}
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                        @if ($this->averageRating() !== null)
                            <span class="text-honey-deep">
                                {{ str_repeat('★', (int) round($this->averageRating())) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - (int) round($this->averageRating())) }}</span>
                            </span>
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">
                                {{ number_format($this->averageRating(), 1) }} ({{ $this->reviewCount() }} {{ \Illuminate\Support\Str::plural('review', $this->reviewCount()) }})
                            </span>
                        @else
                            <span class="text-zinc-500 dark:text-zinc-400">New to the hive · no ratings yet</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button type="button" wire:click="startChat" class="ui-btn ui-btn-sm flex-1 sm:flex-none">
                    <flux:icon name="chat-bubble-left-right" class="size-4" />
                    Message seller
                </button>

                @if ($this->rank())
                    <span class="ui-chip">
                        <span class="size-1.5 rounded-full bg-leaf" aria-hidden="true"></span>
                        BeeRank {{ number_format($this->rank()['score'], 1) }} · {{ $this->rank()['orders'] }} orders / 30d
                    </span>
                @else
                    <span class="ui-chip text-zinc-500 dark:text-zinc-400">Not yet ranked</span>
                @endif
            </div>
        </div>
    </section>

    {{-- Tabs --}}
    <div class="no-scrollbar mt-4 flex gap-2 overflow-x-auto border-b border-ink/15 pb-0 dark:border-zinc-800" role="tablist" aria-label="Store sections">
        @foreach ([['products', 'Products'], ['about', 'About'], ['reviews', 'Reviews']] as [$key, $label])
            <button type="button"
                    role="tab"
                    id="store-tab-{{ $key }}"
                    aria-controls="store-panel-{{ $key }}"
                    :aria-selected="(tab === '{{ $key }}').toString()"
                    @click="tab = '{{ $key }}'"
                    class="relative min-h-11 shrink-0 px-3 pb-3 pt-2 text-sm font-semibold transition"
                    :class="tab === '{{ $key }}' ? 'text-ink dark:text-zinc-100' : 'text-zinc-500 hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200'">
                {{ $label }}
                <span class="absolute inset-x-2 bottom-0 h-[3px] rounded-full bg-honey transition-opacity" :class="tab === '{{ $key }}' ? 'opacity-100' : 'opacity-0'"></span>
            </button>
        @endforeach
    </div>

    {{-- Products --}}
    <section id="store-panel-products" role="tabpanel" aria-labelledby="store-tab-products" x-show="tab === 'products'" class="mt-4">
        @if ($productCategories->isNotEmpty())
            <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0" aria-label="Shop categories">
                <a href="{{ route('categories.index') }}" class="inline-flex min-h-9 shrink-0 items-center rounded-full border border-ink bg-honey px-3.5 py-1.5 text-xs font-semibold text-ink" wire:navigate>
                    All
                </a>
                @foreach ($productCategories as $category)
                    <a href="{{ route('categories.show', $category) }}"
                       class="inline-flex min-h-9 shrink-0 items-center rounded-full border border-ink/40 bg-white px-3.5 py-1.5 text-xs font-medium text-zinc-600 transition hover:border-ink hover:text-ink dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                       wire:navigate>
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($products->isEmpty())
            <x-ui.empty-state class="mt-4" title="No active listings yet" description="This shop is still setting up its shelves." icon="archive-box" />
        @else
            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @php($image = $product->images->first())

                    <a href="{{ route('products.show', [$business->slug, $product->slug]) }}"
                       wire:key="store-product-{{ $product->id }}"
                       class="group flex flex-col overflow-hidden rounded-xl border border-ink/60 bg-white transition duration-200 hover:-translate-y-0.5 hover:border-ink dark:border-zinc-800 dark:bg-zinc-900"
                       wire:navigate>
                        <div class="relative aspect-square overflow-hidden bg-hero dark:bg-zinc-800">
                            <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-15"></div>

                            @if ($image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}"
                                     alt="{{ $image->alt_text ?? $product->name }}"
                                     loading="lazy"
                                     decoding="async"
                                     class="relative size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
                            @else
                                <span class="clip-hex absolute left-1/2 top-1/2 grid size-12 -translate-x-1/2 -translate-y-1/2 place-items-center bg-white/85 font-display text-xl font-semibold text-honey-deep dark:bg-zinc-900/80 dark:text-honey">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>

                        <p class="truncate bg-honey px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.08em] text-ink">
                            {{ $product->category?->name ?? 'Marketplace' }}
                        </p>

                        <div class="flex flex-1 flex-col p-2.5">
                            <p class="line-clamp-2 text-[11px] font-medium leading-tight">{{ $product->name }}</p>
                            <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                                <p class="ui-price text-sm">{{ $product->formattedPrice() }}</p>
                                <span class="rounded-full border border-ink/30 bg-paper px-2 py-0.5 text-[9px] font-medium text-zinc-600 dark:bg-zinc-950 dark:text-zinc-400">
                                    {{ $product->stock }} left
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-5">{{ $products->links() }}</div>
        @endif
    </section>

    {{-- About --}}
    <section id="store-panel-about" role="tabpanel" aria-labelledby="store-tab-about" x-show="tab === 'about'" x-cloak class="mt-4 space-y-4">
        <div class="ui-card bg-paper p-5 dark:bg-zinc-900">
            <h2 class="font-display text-xl font-semibold">About {{ $business->name }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                {{ $business->description ?? 'This business has not added a description yet.' }}
            </p>

            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                <div class="rounded-xl border border-ink/25 bg-white/70 p-3 dark:bg-zinc-950">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Selling since</dt>
                    <dd class="mt-1 font-display text-base font-semibold">{{ $business->created_at?->format('M Y') }}</dd>
                </div>
                <div class="rounded-xl border border-ink/25 bg-white/70 p-3 dark:bg-zinc-950">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Products</dt>
                    <dd class="mt-1 font-display text-base font-semibold">{{ $products->total() }}</dd>
                </div>
                <div class="rounded-xl border border-ink/25 bg-white/70 p-3 dark:bg-zinc-950">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Reviews</dt>
                    <dd class="mt-1 font-display text-base font-semibold">{{ $this->reviewCount() }}</dd>
                </div>
                <div class="rounded-xl border border-ink/25 bg-white/70 p-3 dark:bg-zinc-950">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">BeeRank</dt>
                    <dd class="mt-1 font-display text-base font-semibold">
                        {{ $this->rank() ? number_format($this->rank()['score'], 1) : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="ui-card-honey p-5">
            <h2 class="font-display text-lg font-semibold">How this shop is ranked</h2>
            <p class="mt-2 text-sm text-zinc-700">
                BeeRank looks at the last 30 days only: verified ratings, completed orders, on-time delivery and
                reply speed. A shop that is doing good work now can climb, whatever its age.
            </p>
            <a href="{{ route('ranking') }}" class="ui-btn ui-btn-sm mt-4" wire:navigate>See the leaderboard</a>
        </div>
    </section>

    {{-- Reviews --}}
    <section id="store-panel-reviews" role="tabpanel" aria-labelledby="store-tab-reviews" x-show="tab === 'reviews'" x-cloak class="mt-4 space-y-4">
        <div class="ui-card-honey p-5">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="font-display text-lg font-semibold">Average Rating</h2>
                <p class="text-xs font-medium text-zinc-700">
                    {{ $this->averageRating() !== null ? number_format($this->averageRating(), 1).' based on '.$this->reviewCount().' reviews' : 'No reviews yet' }}
                </p>
            </div>

            <a href="{{ auth()->check() ? route('orders.index') : route('login') }}"
               class="ui-btn ui-btn-sm mt-3"
               wire:navigate>
                <flux:icon name="plus" class="size-4" />
                Write a Review
            </a>

            <ul class="mt-4 space-y-2" aria-label="Rating breakdown">
                @foreach ($this->ratingBreakdown() as $row)
                    <li class="flex items-center gap-3 text-xs">
                        <span class="w-12 shrink-0 font-semibold">{{ $row['star'] }} star{{ $row['star'] === 1 ? '' : 's' }}</span>
                        <span class="h-2.5 flex-1 overflow-hidden rounded-full border border-ink/20 bg-paper">
                            <span class="block h-full rounded-full bg-[#B8860B]" style="width: {{ $row['percent'] }}%"></span>
                        </span>
                        <span class="w-10 text-end font-medium">{{ $row['percent'] }}%</span>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($reviews->isEmpty())
            <x-ui.empty-state title="No ratings yet" description="Ratings open after a delivered order." icon="star" />
        @else
            <ul class="space-y-3">
                @foreach ($reviews as $review)
                    <li class="ui-card bg-paper p-4 dark:bg-zinc-900">
                        <div class="flex items-start gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full border border-ink/50 bg-white font-display text-sm font-semibold text-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->user?->name ?? 'A', 0, 1)) }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold">{{ $review->user?->name ?? 'A customer' }}</p>

                                    @if ($review->isVerifiedPurchase())
                                        <span class="rounded-full border border-leaf/40 bg-leaf/10 px-2 py-0.5 text-[10px] font-medium text-leaf dark:text-emerald-300">
                                            Verified Buyer
                                        </span>
                                    @endif

                                    <span class="ms-auto text-[10px] text-zinc-500 dark:text-zinc-400">{{ $review->created_at?->diffForHumans() }}</span>
                                </div>

                                <p class="mt-0.5 text-xs text-honey-deep">
                                    {{ str_repeat('★', $review->rating) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </p>

                                @if ($review->title)
                                    <p class="mt-2 text-sm font-semibold">{{ $review->title }}</p>
                                @endif

                                @if ($review->body)
                                    <p class="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $review->body }}</p>
                                @endif

                                @if ($review->product)
                                    <p class="mt-2 text-[10px] text-zinc-500 dark:text-zinc-400">on {{ $review->product->name }}</p>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
