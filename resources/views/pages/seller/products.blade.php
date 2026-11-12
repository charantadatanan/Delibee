<?php

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Your products')] class extends Component
{
    public bool $creating = false;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public int $stock = 0;

    public string $category = '';

    public function mount(): void
    {
        $this->authorize('update', $this->business());
    }

    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }

    #[Computed]
    public function products()
    {
        return $this->business()
            ->products()
            ->with('category')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()->active()->orderBy('position')->get();
    }

    public function toggle(int $productId): void
    {
        $product = $this->business()->products()->findOrFail($productId);

        $this->authorize('update', $product);

        $product->update([
            'status' => $product->status === ProductStatus::Active
                ? ProductStatus::Draft
                : ProductStatus::Active,
        ]);

        unset($this->products);
    }

    public function save(): void
    {
        $this->authorize('update', $this->business());

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:4000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string'],
        ]);

        $category = Category::query()->where('slug', $validated['category'])->firstOrFail();

        Product::create([
            'business_id' => $this->business()->id,
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'price_minor' => (int) round(((float) $validated['price']) * 100),
            'currency' => $this->business()->currency,
            'stock' => $validated['stock'],
            'status' => ProductStatus::Active,
        ]);

        $this->reset(['creating', 'name', 'description', 'price', 'stock', 'category']);

        unset($this->products);

        \Flux\Flux::toast('Product listed.', variant: 'success');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';

        do {
            $slug = $base.'-'.Str::lower(Str::random(4));
        } while (Product::where('slug', $slug)->exists());

        return $slug;
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Your products</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Everything you have listed in the marketplace.</p>
        </div>

        <button type="button"
                wire:click="$toggle('creating')"
                class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
            {{ $creating ? 'Close' : 'List a product' }}
        </button>
    </div>

    @if ($creating)
        <form wire:submit="save" class="grid gap-4 rounded-2xl border border-comb/90 bg-white/70 p-5 sm:grid-cols-2 dark:border-zinc-800 dark:bg-zinc-900/70">
            <label class="block sm:col-span-2">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Name</span>
                <input type="text" wire:model="name" placeholder="Golden Mangoes"
                       class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Description</span>
                <textarea wire:model="description" rows="3"
                          class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950"></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Price</span>
                <input type="number" step="0.01" min="0" wire:model="price"
                       class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
            </label>

            <label class="block">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Stock</span>
                <input type="number" min="0" wire:model="stock"
                       class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Category</span>
                <select wire:model="category"
                        class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">Choose a category</option>
                    @foreach ($this->categories() as $category)
                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>

            <div class="sm:col-span-2">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-ink px-4 py-2.5 font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                    <span wire:loading.remove wire:target="save">Publish</span>
                    <span wire:loading wire:target="save">Publishing…</span>
                </button>
            </div>
        </form>
    @endif

    <section class="overflow-hidden rounded-2xl border border-comb/90 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900/70">
        @if ($this->products()->isEmpty())
            <p class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">You have not listed anything yet.</p>
        @else
            <ul class="divide-y divide-comb/70 dark:divide-zinc-800">
                @foreach ($this->products() as $product)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="font-display text-lg font-semibold">{{ $product->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $product->formattedPrice() }}, {{ $product->stock }} in stock
                                @if ($product->category)
                                    <span class="text-zinc-400">/</span> {{ $product->category->name }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $product->status === \App\Enums\ProductStatus::Active
                                    ? 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300'
                                    : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $product->status->label() }}
                            </span>

                            <button type="button"
                                    wire:click="toggle({{ $product->id }})"
                                    class="text-sm font-medium text-honey-deep hover:underline dark:text-honey">
                                {{ $product->status === \App\Enums\ProductStatus::Active ? 'Unlist' : 'List' }}
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
