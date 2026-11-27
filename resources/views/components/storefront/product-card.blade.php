@props(['product'])

<a href="{{ route('products.show', [$product->business->slug, $product->slug]) }}"
   class="group flex flex-col overflow-hidden rounded-2xl border border-ink/70 bg-white transition duration-200 hover:-translate-y-0.5 hover:border-ink dark:border-zinc-800 dark:bg-zinc-900"
   wire:navigate>
    <div class="relative grid aspect-square place-items-center overflow-hidden bg-hero dark:bg-zinc-800">
        <div class="absolute inset-0 bg-comb opacity-60 dark:opacity-15"></div>

        @if ($product->images->isNotEmpty())
            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}"
                 alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                 loading="lazy"
                 decoding="async"
                 class="relative size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
        @else
            <span class="clip-hex relative grid size-16 place-items-center border border-ink/10 bg-white/85 font-display text-2xl font-semibold text-honey-deep shadow-sm dark:bg-zinc-900/80 dark:text-honey">
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
            </span>
        @endif

        @unless ($product->isInStock())
            <span class="absolute start-3 top-3 rounded-full border border-ink bg-ink px-2.5 py-1 text-xs font-semibold text-paper dark:bg-zinc-100 dark:text-zinc-900">
                Sold out
            </span>
        @endunless
    </div>

    <div class="flex flex-1 flex-col">
        <p class="bg-honey px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink">
            {{ $product->category?->name ?? 'Marketplace' }}
        </p>

        <div class="flex flex-1 flex-col gap-1.5 p-4">
            <p class="flex items-center gap-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                <span class="clip-hex grid size-5 place-items-center bg-comb/80 text-[10px] font-bold text-honey-deep dark:bg-zinc-800 dark:text-honey">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->business->name, 0, 1)) }}
                </span>
                {{ $product->business->name }}
            </p>

            <h3 class="font-display text-lg font-semibold leading-snug tracking-tight transition-colors group-hover:text-honey-deep dark:group-hover:text-honey">
                {{ $product->name }}
            </h3>

            <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                <p class="ui-price text-lg">{{ $product->formattedPrice() }}</p>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                    {{ $product->stock }} left
                </span>
            </div>
        </div>
    </div>
</a>
