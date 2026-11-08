<div class="grid gap-8 lg:grid-cols-2">
    <div class="grid gap-3">
        <div class="grid aspect-square place-items-center rounded-xl border border-zinc-200 bg-white text-6xl dark:border-zinc-700 dark:bg-zinc-800">
            @if ($product->images->isNotEmpty())
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}"
                     alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                     class="size-full rounded-xl object-cover">
            @else
                🍯
            @endif
        </div>

        @if ($product->images->count() > 1)
            <div class="grid grid-cols-4 gap-3">
                @foreach ($product->images->skip(1) as $image)
                    <div class="grid aspect-square place-items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}" alt="{{ $image->alt_text }}" class="size-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <p class="text-sm uppercase tracking-wide text-zinc-500">
            <a href="{{ route('businesses.show', $business->slug) }}" class="hover:text-amber-600" wire:navigate>
                {{ $business->name }}
            </a>
            @if ($business->isRising())
                <span class="ms-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Rising Bee</span>
            @endif
        </p>

        <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $product->name }}</h1>

        <p class="mt-3 text-2xl font-semibold">{{ $product->formattedPrice() }}</p>

        @if ($product->description)
            <p class="mt-4 leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $product->description }}</p>
        @endif

        <dl class="mt-6 space-y-1 text-sm">
            <div class="flex gap-2">
                <dt class="w-24 text-zinc-500">Category</dt>
                <dd>{{ $product->category?->name ?? '—' }}</dd>
            </div>
            <div class="flex gap-2">
                <dt class="w-24 text-zinc-500">Availability</dt>
                <dd class="{{ $product->isInStock() ? '' : 'text-red-600' }}">
                    {{ $product->isInStock() ? $product->stock.' in stock' : 'Out of stock' }}
                </dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">Quantity</span>
                <input type="number" min="1" max="{{ $product->stock }}" wire:model="quantity"
                       class="mt-1 w-24 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
            </label>

            <button type="button"
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    @disabled(! $product->isInStock())
                    class="rounded-lg bg-amber-500 px-5 py-2.5 font-semibold text-white hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="addToCart">Add to Honey Pot</span>
                <span wire:loading wire:target="addToCart">Adding…</span>
            </button>
        </div>

        @if ($this->inCart() > 0)
            <p class="mt-2 text-sm text-zinc-500">
                {{ $this->inCart() }} already in your
                <a href="{{ route('cart.index') }}" class="underline" wire:navigate>Honey Pot</a>.
            </p>
        @endif
    </div>

    <div class="lg:col-span-2">
        <h2 class="text-lg font-semibold">Honey Rating</h2>

        @if ($reviews->isEmpty())
            <p class="mt-2 text-sm text-zinc-500">No reviews for this product yet.</p>
        @else
            <ul class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($reviews as $review)
                    <li class="py-4">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $review->user?->name ?? 'A customer' }}</span>
                            <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-zinc-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                            @if ($review->isVerifiedPurchase())
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Verified purchase</span>
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
</div>
