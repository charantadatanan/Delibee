<div>
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-start gap-4">
            <div class="grid size-16 place-items-center rounded-xl bg-amber-100 text-3xl">🏪</div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight">{{ $business->name }}</h1>
                    @if ($business->isRising())
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                            Rising Bee
                        </span>
                    @endif
                </div>

                @if ($business->description)
                    <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $business->description }}</p>
                @endif

                <p class="mt-2 text-xs text-zinc-500">
                    Selling on deliBEE since {{ $business->created_at?->format('F Y') }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 text-center dark:border-zinc-700">
                @if ($this->rank())
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($this->rank()['score'], 1) }}</p>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">BeeRank score</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ $this->rank()['orders'] }} orders in the last 30 days</p>
                @else
                    <p class="text-lg font-semibold text-zinc-400">—</p>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Not yet ranked</p>
                    <p class="mt-1 text-xs text-zinc-500">Needs {{ config('beerank.minimum_orders') }} orders in the last 30 days</p>
                @endif
            </div>
        </div>

        @if ($this->averageRating() !== null)
            <p class="mt-4 text-sm">
                <span class="text-amber-500">{{ str_repeat('★', (int) round($this->averageRating())) }}</span>
                <span class="ms-1 text-zinc-600 dark:text-zinc-400">
                    {{ number_format($this->averageRating(), 1) }} average from {{ $business->reviews()->published()->count() }} ratings
                </span>
            </p>
        @endif
    </div>

    <h2 class="mt-8 text-lg font-semibold">Products</h2>

    @if ($products->isEmpty())
        <p class="mt-2 text-sm text-zinc-500">This business has no active listings yet.</p>
    @else
        <div class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    @endif

    <h2 class="mt-10 text-lg font-semibold">Honey Rating</h2>

    @if ($reviews->isEmpty())
        <p class="mt-2 text-sm text-zinc-500">No ratings for this business yet.</p>
    @else
        <ul class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach ($reviews as $review)
                <li class="py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ $review->user?->name ?? 'A customer' }}</span>
                        <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-zinc-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        @if ($review->isVerifiedPurchase())
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Verified purchase</span>
                        @endif
                        @if ($review->product)
                            <span class="text-xs text-zinc-500">on {{ $review->product->name }}</span>
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
</div>
