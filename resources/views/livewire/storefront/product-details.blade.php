<div class="grid gap-8 lg:grid-cols-[1.02fr_0.98fr] lg:gap-12">
    <div>
        <div class="relative grid aspect-[4/3] place-items-center overflow-hidden rounded-[28px] border border-ink/60 bg-hero dark:border-zinc-800 dark:bg-zinc-900 lg:aspect-square">
            <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-10"></div>

            @if ($product->images->isNotEmpty())
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}"
                     alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                     class="relative size-full object-cover">
            @else
                <span class="clip-hex relative grid size-28 place-items-center border border-ink/10 bg-white/85 font-display text-5xl font-semibold text-honey-deep shadow-sm dark:bg-zinc-900/80 dark:text-honey">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                </span>
            @endif

            <span class="absolute end-3 top-3 rounded-full border px-3 py-1 text-[11px] font-semibold
                {{ $product->isInStock() ? 'border-leaf/50 bg-[#E4F2DA] text-leaf' : 'border-red-300 bg-red-50 text-red-700' }}">
                {{ $product->isInStock() ? 'In Stock' : 'Out of Stock' }}
            </span>

            <a href="{{ route('businesses.show', $business->slug) }}"
               class="absolute start-3 top-3 inline-flex items-center gap-2 rounded-full border border-ink/50 bg-paper/90 px-2.5 py-1 text-[11px] font-semibold backdrop-blur transition hover:bg-honey"
               wire:navigate>
                <span class="grid size-5 place-items-center rounded-full bg-honey text-[9px] font-bold text-ink">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                </span>
                {{ $business->name }}
            </a>
        </div>

        @if ($product->images->count() > 1)
            <div class="mt-3 grid grid-cols-4 gap-3">
                @foreach ($product->images->skip(1) as $image)
                    <div class="relative aspect-square overflow-hidden rounded-xl border border-ink/40 bg-hero dark:border-zinc-800 dark:bg-zinc-900">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}"
                             alt="{{ $image->alt_text ?? $product->name }}"
                             class="size-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="lg:sticky lg:top-24 lg:h-fit">
        <div class="ui-card bg-paper p-5 dark:bg-zinc-900 sm:p-6">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ route('businesses.show', $business->slug) }}"
                   class="inline-flex min-h-8 items-center gap-2 rounded-full border border-ink/40 bg-white px-3 py-1 font-medium transition hover:border-ink dark:border-zinc-700 dark:bg-zinc-950"
                   wire:navigate>
                    {{ $business->name }}
                </a>

                @if ($business->isRising())
                    <span class="rounded-full border border-leaf/40 bg-leaf/10 px-2.5 py-1 font-semibold text-leaf dark:text-emerald-300">
                        Rising Bee
                    </span>
                @endif

                @if ($product->category)
                    <span class="ui-chip">{{ $product->category->name }}</span>
                @endif
            </div>

            <h1 class="mt-4 font-display text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $product->name }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                @if ($averageRating !== null)
                    <span class="text-honey-deep">
                        {{ str_repeat('★', (int) round($averageRating)) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - (int) round($averageRating)) }}</span>
                    </span>
                    <span class="font-medium">{{ number_format((float) $averageRating, 1) }} ({{ $reviewCount }})</span>
                @else
                    <span class="text-zinc-500 dark:text-zinc-400">No ratings yet · ratings open after delivery</span>
                @endif
            </div>

            <p class="mt-4 font-display text-3xl font-semibold text-ink dark:text-honey">{{ $product->formattedPrice() }}</p>

            @if ($product->description)
                <p class="mt-4 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $product->description }}</p>
            @endif

            <div class="mt-5 flex flex-wrap items-end gap-3 border-t border-ink/15 pt-5 dark:border-zinc-800">
                <div>
                    <span class="ui-label">Quantity</span>
                    <div class="mt-1.5 inline-flex items-center rounded-full border border-ink/60 bg-white dark:border-zinc-700 dark:bg-zinc-950" role="group" aria-label="Quantity">
                        <button type="button"
                                wire:click="decrement"
                                @disabled($quantity <= 1)
                                class="grid size-11 place-items-center rounded-full text-lg font-semibold transition hover:bg-hero disabled:opacity-40"
                                aria-label="Decrease quantity">−</button>
                        <span class="min-w-10 text-center text-sm font-semibold" aria-live="polite">{{ $quantity }}</span>
                        <button type="button"
                                wire:click="increment"
                                @disabled($quantity >= $product->stock)
                                class="grid size-11 place-items-center rounded-full text-lg font-semibold transition hover:bg-hero disabled:opacity-40"
                                aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <button type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        @disabled(! $product->isInStock())
                        class="ui-btn flex-1 whitespace-nowrap">
                    <flux:icon name="shopping-bag" class="size-4 shrink-0" />
                    <span wire:loading.remove wire:target="addToCart">Add to Cart</span>
                    <span wire:loading wire:target="addToCart">Adding…</span>
                </button>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                @if (auth()->id() !== $business->owner_id)
                    <button type="button" wire:click="startChat" class="ui-btn ui-btn-ghost ui-btn-sm">
                        <flux:icon name="chat-bubble-left-right" class="size-4" />
                        Message seller
                    </button>
                @endif

                <span class="ui-chip self-center">
                    <span class="size-1.5 rounded-full {{ $product->stock > 5 ? 'bg-leaf' : 'bg-[#B77C00]' }}" aria-hidden="true"></span>
                    {{ $product->stock }} in stock
                </span>
            </div>

            @if ($this->inCart() > 0)
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $this->inCart() }} already in your
                    <a href="{{ route('cart.index') }}" class="font-semibold text-honey-deep underline underline-offset-4 dark:text-honey" wire:navigate>Honey Pot</a>.
                </p>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2">
        <section class="ui-card bg-paper p-5 dark:bg-zinc-900 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-2xl font-semibold">Honey rating</h2>
                @if ($averageRating !== null)
                    <span class="ui-chip">{{ number_format((float) $averageRating, 1) }} / 5 · {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                @endif
            </div>

            @if ($reviews->isEmpty())
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    No ratings for this product yet. Ratings open after a delivered order.
                </p>
            @else
                <ul class="mt-4 divide-y divide-ink/10 dark:divide-zinc-800">
                    @foreach ($reviews as $review)
                        <li class="py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium">{{ $review->user?->name ?? 'A customer' }}</span>
                                <span class="text-honey-deep">{{ str_repeat('★', $review->rating) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                                @if ($review->isVerifiedPurchase())
                                    <span class="rounded-full border border-leaf/40 bg-leaf/10 px-2 py-0.5 text-[10px] font-medium text-leaf dark:text-emerald-300">Verified purchase</span>
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
</div>
