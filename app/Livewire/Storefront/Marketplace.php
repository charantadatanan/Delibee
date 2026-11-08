<?php

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Marketplace extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'category', except: '')]
    public string $category = '';

    #[Url(as: 'min', except: '')]
    public string $minPrice = '';

    #[Url(as: 'max', except: '')]
    public string $maxPrice = '';

    #[Url(as: 'sort', except: '')]
    public string $sort = '';

    /**
     * The sorts offered in the filter panel.
     *
     * @var list<array{value: string, label: string}>
     */
    public const SORTS = [
        ['value' => 'newest', 'label' => 'Newest first'],
        ['value' => 'price_asc', 'label' => 'Price: low to high'],
        ['value' => 'price_desc', 'label' => 'Price: high to low'],
        ['value' => 'relevance', 'label' => 'Best match'],
    ];

    public function updated(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'minPrice', 'maxPrice', 'sort']);

        $this->resetPage();
    }

    public function render(): View
    {
        $categories = Category::query()->active()->orderBy('position')->get();

        $products = Product::query()
            ->active()
            ->with(['business', 'category', 'images'])
            ->search($this->search)
            ->when($this->category !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $this->category),
            ))
            ->when($this->minPrice !== '', fn ($query) => $query->where('price_minor', '>=', $this->minPriceMinor()))
            ->when($this->maxPrice !== '', fn ($query) => $query->where('price_minor', '<=', $this->maxPriceMinor()));

        $this->applySort($products);

        return view('livewire.storefront.marketplace', [
            'products' => $products->paginate(12),
            'categories' => $categories,
        ]);
    }

    /**
     * Searching defaults to relevance so the closest match is first; with no
     * search term the newest listings lead.
     */
    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query): void
    {
        $sort = $this->sort !== '' ? $this->sort : ($this->search !== '' ? 'relevance' : 'newest');

        match ($sort) {
            'price_asc' => $query->orderBy('price_minor'),
            'price_desc' => $query->orderByDesc('price_minor'),
            'relevance' => $query->orderByRelevance($this->search),
            default => $query->orderByDesc('created_at'),
        };
    }

    private function minPriceMinor(): int
    {
        return (int) round(((float) $this->minPrice) * 100);
    }

    private function maxPriceMinor(): int
    {
        return (int) round(((float) $this->maxPrice) * 100);
    }
}
