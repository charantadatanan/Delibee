<?php

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Inventory')] class extends Component
{
    public bool $creating = false;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public int $stock = 0;

    public string $category = '';

    public ?int $restockingId = null;

    public int $restockQuantity = 25;

    public function mount(): void
    {
        $this->authorize('update', $this->business());

        $restockId = request()->integer('restock');

        if ($restockId > 0) {
            $product = $this->business()->products()->find($restockId);

            if ($product !== null) {
                $this->restockingId = $product->id;
            }
        }
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
            ->with(['category', 'images'])
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()->active()->orderBy('position')->get();
    }

    #[Computed]
    public function restockingProduct(): ?Product
    {
        if ($this->restockingId === null) {
            return null;
        }

        return $this->business()
            ->products()
            ->with('images')
            ->find($this->restockingId);
    }

    #[Computed]
    public function lowStockCount(): int
    {
        return $this->business()
            ->products()
            ->where('stock', '<=', 5)
            ->count();
    }

    public function openRestock(int $productId): void
    {
        $product = $this->business()->products()->findOrFail($productId);

        $this->authorize('update', $product);

        $this->restockingId = $product->id;
        $this->restockQuantity = 25;
    }

    public function closeRestock(): void
    {
        $this->restockingId = null;
    }

    public function incrementRestock(): void
    {
        $this->restockQuantity = min(999, $this->restockQuantity + 1);
    }

    public function decrementRestock(): void
    {
        $this->restockQuantity = max(1, $this->restockQuantity - 1);
    }

    public function confirmRestock(): void
    {
        $product = $this->restockingProduct();

        if ($product === null) {
            return;
        }

        $this->authorize('update', $product);

        $validated = $this->validate([
            'restockQuantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $product->increment('stock', (int) $validated['restockQuantity']);

        unset($this->products, $this->restockingProduct, $this->lowStockCount);

        $this->restockingId = null;
        $this->restockQuantity = 25;

        \Flux\Flux::toast('Stock updated.', variant: 'success');
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

        unset($this->products, $this->lowStockCount);

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

@php
    $business = $this->business();
    $restocking = $this->restockingProduct();
@endphp

<div class="space-y-5">
    <x-ui.mobile-header :title="__('Inventory')" :back="route('seller.dashboard')" />

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Seller desk</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Inventory</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Products, stock and restock in one place.</p>
        </div>

        <button type="button"
                wire:click="$toggle('creating')"
                class="ui-btn ui-btn-sm ms-auto">
            <flux:icon name="{{ $creating ? 'x-mark' : 'plus' }}" class="size-4" />
            {{ $creating ? 'Close' : 'Add New Product' }}
        </button>
    </div>

    @if ($this->lowStockCount() > 0)
        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-[#D9AE55] bg-[#FFF1CE] p-4 text-[#5A3A00] dark:border-[#7A5A12] dark:bg-[#2B230F] dark:text-[#F0C674]">
            <span class="grid size-10 shrink-0 place-items-center rounded-full border border-[#B77C00]/40 bg-[#FFE0A3]">
                <flux:icon name="exclamation-triangle" class="size-5 text-[#8A5A00]" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-display text-base font-semibold">{{ $this->lowStockCount() }} {{ \Illuminate\Support\Str::plural('item', $this->lowStockCount()) }} running low</p>
                <p class="text-xs">Please restock soon so the shop stays open for orders.</p>
            </div>
            <a href="#inventory" class="ui-btn ui-btn-sm">Review stock</a>
        </div>
    @endif

    @if ($restocking)
        <section id="restock" class="ui-card-honey scroll-mt-24 p-4 sm:p-5" aria-label="Restock {{ $restocking->name }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-600">Inventory</p>
                    <h2 class="mt-0.5 font-display text-2xl font-semibold">Restock Item</h2>
                </div>
                <button type="button" wire:click="closeRestock" class="grid size-9 place-items-center rounded-full border border-ink/40 bg-white/70" aria-label="Close restock panel">
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            </div>

            <div class="mt-4 flex items-center gap-3 rounded-xl border border-ink/40 bg-white/70 p-3 dark:bg-zinc-950">
                <span class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-lg border border-ink/40 bg-hero dark:border-zinc-700 dark:bg-zinc-900">
                    @if ($restocking->images->isNotEmpty())
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($restocking->images->first()->path) }}" alt="" class="size-full object-cover">
                    @else
                        <span class="font-display text-xl font-semibold text-honey-deep dark:text-honey">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($restocking->name, 0, 1)) }}
                        </span>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-display text-lg font-semibold">{{ $restocking->name }}</p>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ $restocking->formattedPrice() }} · {{ $restocking->category?->name ?? 'Uncategorised' }}</p>
                    <span class="mt-1 inline-block rounded-full border border-ink/30 bg-paper px-2 py-0.5 text-[10px] font-semibold">
                        {{ $restocking->stock }} in stock
                    </span>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="ui-label">Quantity to Add</p>
                    <div class="mt-1.5 inline-flex items-center rounded-full border border-ink/60 bg-white dark:border-zinc-700 dark:bg-zinc-950" role="group" aria-label="Quantity to add">
                        <button type="button" wire:click="decrementRestock" class="grid size-11 place-items-center rounded-full text-lg font-semibold transition hover:bg-hero" aria-label="Decrease restock quantity">−</button>
                        <span class="min-w-14 text-center font-display text-xl font-semibold" aria-live="polite">+{{ $restockQuantity }}</span>
                        <button type="button" wire:click="incrementRestock" class="grid size-11 place-items-center rounded-full text-lg font-semibold transition hover:bg-hero" aria-label="Increase restock quantity">+</button>
                    </div>
                </div>

                <dl class="rounded-xl border border-ink/30 bg-white/70 p-3 text-xs dark:bg-zinc-950">
                    <div class="flex justify-between gap-2 py-0.5">
                        <dt class="text-zinc-500 dark:text-zinc-400">Current</dt>
                        <dd class="font-medium">{{ $restocking->stock }}</dd>
                    </div>
                    <div class="flex justify-between gap-2 py-0.5">
                        <dt class="text-zinc-500 dark:text-zinc-400">Add</dt>
                        <dd class="font-medium">+{{ $restockQuantity }}</dd>
                    </div>
                    <div class="mt-1 flex justify-between gap-2 border-t border-ink/15 pt-1 text-sm font-semibold dark:border-zinc-800">
                        <dt>New stock total</dt>
                        <dd>{{ $restocking->stock + $restockQuantity }}</dd>
                    </div>
                </dl>
            </div>

            <button type="button" wire:click="confirmRestock" class="ui-btn mt-4 w-full">
                Confirm Restock
            </button>
        </section>
    @endif

    @if ($creating)
        <form wire:submit="save" class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-5">
            <h2 class="font-display text-xl font-semibold">Add New Product</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">New listings go live as soon as you publish.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="ui-label">Name</span>
                    <input type="text" wire:model="name" placeholder="Golden Mangoes" class="ui-field mt-1.5">
                    @error('name') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                </label>

                <label class="block sm:col-span-2">
                    <span class="ui-label">Description</span>
                    <textarea wire:model="description" rows="3" class="ui-field mt-1.5" placeholder="What makes it good?"></textarea>
                    @error('description') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="ui-label">Price</span>
                    <input type="number" step="0.01" min="0" wire:model="price" class="ui-field mt-1.5">
                    @error('price') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="ui-label">Stock</span>
                    <input type="number" min="0" wire:model="stock" class="ui-field mt-1.5">
                    @error('stock') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                </label>

                <label class="block sm:col-span-2">
                    <span class="ui-label">Category</span>
                    <select wire:model="category" class="ui-field mt-1.5">
                        <option value="">Choose a category</option>
                        @foreach ($this->categories() as $category)
                            <option value="{{ $category->slug }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
                </label>
            </div>

            <button type="submit" wire:loading.attr="disabled" class="ui-btn mt-4">
                <span wire:loading.remove wire:target="save">Publish product</span>
                <span wire:loading wire:target="save">Publishing…</span>
            </button>
        </form>
    @endif

    <section id="inventory" class="scroll-mt-24">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-display text-xl font-semibold">Your products</h2>
            <span class="ui-chip">{{ $this->products()->count() }} listed</span>
        </div>

        @if ($this->products()->isEmpty())
            <x-ui.empty-state class="mt-3" title="No products yet" description="Add your first product to open the shelves." icon="archive-box">
                <button type="button" wire:click="$set('creating', true)" class="ui-btn">Add New Product</button>
            </x-ui.empty-state>
        @else
            <div class="mt-3 grid gap-3 lg:grid-cols-2">
                @foreach ($this->products() as $product)
                    <article class="ui-card bg-paper p-3.5 dark:bg-zinc-900">
                        <div class="flex items-start gap-3">
                            <span class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-ink/40 bg-hero dark:border-zinc-700 dark:bg-zinc-950">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}" alt="" loading="lazy" class="size-full object-cover">
                                @else
                                    <span class="font-display text-xl font-semibold text-honey-deep dark:text-honey">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                    </span>
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start gap-2">
                                    <p class="min-w-0 flex-1 truncate font-display text-base font-semibold">{{ $product->name }}</p>
                                    <span class="rounded-full border px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide
                                        {{ $product->status === ProductStatus::Active ? 'border-leaf/40 bg-leaf/10 text-leaf dark:text-emerald-300' : 'border-ink/20 bg-white/60 text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400' }}">
                                        {{ $product->status->label() }}
                                    </span>
                                </div>

                                <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">{{ $product->category?->name ?? 'Uncategorised' }}</p>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                    <span class="ui-price">{{ $product->formattedPrice() }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-semibold
                                        {{ $product->stock <= 5 ? 'border-[#D9AE55] bg-[#FFF1CE] text-[#7A4B00]' : 'border-leaf/40 bg-leaf/10 text-leaf dark:text-emerald-300' }}">
                                        Stock: {{ $product->stock }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="openRestock({{ $product->id }})" class="ui-btn ui-btn-sm">
                                Restock
                            </button>

                            <button type="button" wire:click="toggle({{ $product->id }})" class="ui-btn ui-btn-ghost ui-btn-sm">
                                {{ $product->status === ProductStatus::Active ? 'Unlist' : 'List' }}
                            </button>

                            <a href="{{ route('products.show', [$business->slug, $product->slug]) }}" class="inline-flex min-h-9 items-center gap-1 rounded-full px-3 text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                                View
                                <flux:icon name="arrow-up-right" class="size-3.5" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @php($productCount = $this->products()->count())
    @php($lowStock = $this->lowStockCount())
    @php($healthyStock = max(0, $productCount - $lowStock))
    @php($healthyPercent = $productCount > 0 ? (int) round(($healthyStock / $productCount) * 100) : 0)

    @if ($productCount > 0)
        <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
            <h2 class="font-display text-xl font-semibold">Stock Overview</h2>

            <div class="mt-4 flex flex-wrap items-center gap-5">
                <div class="grid size-28 place-items-center rounded-full border border-ink/30"
                     style="background: conic-gradient(var(--color-honey) 0 {{ $healthyPercent }}%, var(--color-comb) {{ $healthyPercent }}% 100%);">
                    <div class="grid size-20 place-items-center rounded-full border border-ink/20 bg-paper text-center dark:bg-zinc-950">
                        <div>
                            <p class="font-display text-xl font-semibold">{{ $healthyPercent }}%</p>
                            <p class="text-[9px] text-zinc-500 dark:text-zinc-400">healthy</p>
                        </div>
                    </div>
                </div>

                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-honey" aria-hidden="true"></span>
                        In Stock <span class="font-semibold">{{ $healthyStock }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-comb" aria-hidden="true"></span>
                        Low Stock <span class="font-semibold">{{ $lowStock }}</span>
                    </li>
                </ul>
            </div>
        </section>
    @endif
</div>
