@props(['product'])

<a href="{{ route('products.show', [$product->business->slug, $product->slug]) }}"
   class="group flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition hover:border-amber-400 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800"
   wire:navigate>
    <div class="grid aspect-[4/3] place-items-center bg-zinc-100 text-4xl dark:bg-zinc-700">
        @if ($product->images->isNotEmpty())
            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}"
                 alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                 class="size-full object-cover">
        @else
            🍯
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-1 p-4">
        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
            {{ $product->business->name }}
        </p>
        <h3 class="font-medium leading-snug group-hover:text-amber-600 dark:group-hover:text-amber-400">
            {{ $product->name }}
        </h3>
        <p class="mt-auto pt-2 font-semibold">{{ $product->formattedPrice() }}</p>
        @unless ($product->isInStock())
            <p class="text-xs font-medium text-red-600">Out of stock</p>
        @endunless
    </div>
</a>
