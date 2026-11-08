<div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-12">
    <div>
        <div class="relative grid aspect-square place-items-center overflow-hidden rounded-3xl border border-comb bg-[#f6ecd4] dark:border-zinc-800 dark:bg-zinc-900">
            <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-10"></div>

            @if ($product->images->isNotEmpty())
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}"
                     alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                     class="relative size-full object-cover">
            @else
                <span class="clip-hex relative grid size-24 place-items-center bg-white/85 font-display text-4xl font-semibold text-honey-deep shadow-sm dark:bg-zinc-900/80 dark:text-honey">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                </span>
            @endif
        </div>

        @if ($product->images->count() > 1)
            <div class="mt-3 grid grid-cols-4 gap-3">
                @foreach ($product->images->skip(1) as $image)
                    <div class="relative aspect-square overflow-hidden rounded-xl border border-comb bg-[#f6ecd4] dark:border-zinc-800 dark:bg-zinc-900">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}"
                             alt="{{ $image->alt_text ?? $product->name }}"
                             class="size-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="lg:sticky lg:top-24 lg:h-fit">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('businesses.show', $business->slug) }}"
               class="inline-flex items-center gap-2 font-medium text-zinc-600 transition hover:text-honey-deep dark:text-zinc-400 dark:hover:text-honey"
               wire:navigate>
                <span class="clip-hex grid size-6 place-items-center bg-honey text-[11px] font-bold text-ink">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                </span>
                {{ $business->name }}
            </a>

            @if ($business->isRising())
                <span class="rounded-full bg-leaf/10 px-2.5 py-1 text-xs font-semibold text-leaf dark:bg-leaf/20 dark:text-emerald-300">
                    Rising Bee
                </span>
            @endif
        </div>

        <h1 class="mt-3 font-display text-4xl font-semibold leading-tight tracking-tight">{{ $product->name }}</h1>

        <p class="mt-3 font-display text-2xl font-semibold text-honey-deep dark:text-honey">{{ $product->formattedPrice() }}</p>

        @if ($product->description)
            <p class="mt-4 leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $product->description }}</p>
        @endif

        <dl class="mt-6 grid gap-4 rounded-2xl border border-comb/90 bg-white/60 p-4 text-sm sm:grid-cols-2 dark:border-zinc-800 dark:bg-zinc-900/60">
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Category</dt>
                <dd class="mt-0.5 font-medium">{{ $product->category?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Availability</dt>
                <dd class="mt-0.5 font-medium {{ $product->isInStock() ? 'text-leaf dark:text-emerald-300' : 'text-red-600' }}">
                    {{ $product->isInStock() ? $product->stock.' in stock' : 'Out of stock' }}
                </dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-comb/90 bg-white/60 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
            <label class="block">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Quantity</span>
                <input type="number" min="1" max="{{ $product->stock }}" wire:model="quantity"
                       class="mt-1.5 w-24 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
            </label>

            <button type="button"
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    @disabled(! $product->isInStock())
                    class="rounded-lg bg-ink px-5 py-2.5 font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                <span wire:loading.remove wire:target="addToCart">Add to Honey Pot</span>
                <span wire:loading wire:target="addToCart">Adding…</span>
            </button>

            @if (auth()->id() !== $business->owner_id)
                <button type="button"
                        wire:click="startChat"
                        class="rounded-lg border border-comb bg-white/60 px-5 py-2.5 font-semibold text-ink transition hover:border-honey-deep/40 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:border-honey/40">
                    Message seller
                </button>
            @endif
        </div>

        @if ($this->inCart() > 0)
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $this->inCart() }} already in your
                <a href="{{ route('cart.index') }}" class="font-medium text-honey-deep underline underline-offset-4 dark:text-honey" wire:navigate>Honey Pot</a>.
            </p>
        @endif
    </div>

    <div class="lg:col-span-2">
        <section class="rounded-2xl border border-comb/90 bg-white/60 p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/60">
            <h2 class="font-display text-2xl font-semibold">Honey rating</h2>

            @if ($reviews->isEmpty())
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    No ratings for this product yet. Ratings open after a delivered order.
                </p>
            @else
                <ul class="mt-4 divide-y divide-comb/70 dark:divide-zinc-800">
                    @foreach ($reviews as $review)
                        <li class="py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium">{{ $review->user?->name ?? 'A customer' }}</span>
                                <span class="text-honey">{{ str_repeat('★', $review->rating) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                                @if ($review->isVerifiedPurchase())
                                    <span class="rounded-full bg-leaf/10 px-2 py-0.5 text-xs font-medium text-leaf dark:bg-leaf/20 dark:text-emerald-300">Verified purchase</span>
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
