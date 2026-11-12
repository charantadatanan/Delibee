<div>
    <section class="relative overflow-hidden rounded-3xl border border-comb bg-[#FFF4C5] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

        <div class="relative grid gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="flex flex-wrap items-start gap-4">
                <span class="clip-hex grid size-16 shrink-0 place-items-center bg-honey font-display text-3xl font-bold text-ink">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-display text-3xl font-semibold tracking-tight">{{ $business->name }}</h1>

                        @if ($business->isRising())
                            <span class="rounded-full bg-leaf/10 px-2.5 py-1 text-xs font-semibold text-leaf dark:bg-leaf/20 dark:text-emerald-300">
                                Rising Bee
                            </span>
                        @endif
                    </div>

                    @if ($business->description)
                        <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $business->description }}</p>
                    @endif

                    <p class="mt-3 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        Selling on deliBEE since {{ $business->created_at?->format('F Y') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-comb/90 bg-paper/85 p-5 text-center dark:border-zinc-700 dark:bg-zinc-950/60 lg:min-w-56">
                @if ($this->rank())
                    <p class="font-display text-5xl font-semibold text-honey-deep dark:text-honey">
                        {{ number_format($this->rank()['score'], 1) }}
                    </p>
                    <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">BeeRank score</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $this->rank()['orders'] }} orders in the last 30 days</p>
                @else
                    <p class="font-display text-4xl font-semibold text-zinc-400 dark:text-zinc-600">—</p>
                    <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Not yet ranked</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Needs {{ config('beerank.minimum_orders') }} orders in 30 days
                    </p>
                @endif
            </div>
        </div>

        @if ($this->averageRating() !== null)
            <div class="relative border-t border-comb/70 px-6 py-3 text-sm dark:border-zinc-800 sm:px-8">
                <span class="text-honey">{{ str_repeat('★', (int) round($this->averageRating())) }}</span>
                <span class="ms-2 text-zinc-600 dark:text-zinc-400">
                    {{ number_format($this->averageRating(), 1) }} average from {{ $business->reviews()->published()->count() }} verified ratings
                </span>
            </div>
        @endif
    </section>

    <section class="mt-10">
        <h2 class="font-display text-2xl font-semibold">Products</h2>

        @if ($products->isEmpty())
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">This business has no active listings yet.</p>
        @else
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>

    <section class="mt-10">
        <h2 class="font-display text-2xl font-semibold">Honey rating</h2>

        @if ($reviews->isEmpty())
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No ratings for this business yet.</p>
        @else
            <ul class="mt-4 divide-y divide-comb/70 rounded-2xl border border-comb/90 bg-white/60 px-5 dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900/60">
                @foreach ($reviews as $review)
                    <li class="py-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium">{{ $review->user?->name ?? 'A customer' }}</span>
                            <span class="text-honey">{{ str_repeat('★', $review->rating) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span></span>

                            @if ($review->isVerifiedPurchase())
                                <span class="rounded-full bg-leaf/10 px-2 py-0.5 text-xs font-medium text-leaf dark:bg-leaf/20 dark:text-emerald-300">
                                    Verified purchase
                                </span>
                            @endif

                            @if ($review->product)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">on {{ $review->product->name }}</span>
                            @endif
                        </div>

                        @if ($review->title)
                            <p class="mt-1 font-medium">{{ $review->title }}</p>
                        @endif

                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $review->body }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
