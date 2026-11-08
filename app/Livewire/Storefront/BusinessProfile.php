<?php

namespace App\Livewire\Storefront;

use App\Models\Business;
use App\Services\BeeRankService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessProfile extends Component
{
    public Business $business;

    use WithPagination;

    public function mount(Business $business): void
    {
        $this->business = $business->load(['owner']);
    }

    /**
     * The live score for this business, or null when it has not yet traded
     * enough to be ranked.
     *
     * @return array{score: float, components: array<string, float>, orders: int}|null
     */
    #[Computed]
    public function rank(): ?array
    {
        $result = app(BeeRankService::class)->evaluate($this->business);

        if ($result === null) {
            return null;
        }

        return [
            'score' => $result->score,
            'components' => $result->components,
            'orders' => $result->orderCount,
        ];
    }

    #[Computed]
    public function averageRating(): ?float
    {
        $average = $this->business->reviews()->published()->avg('rating');

        return $average === null ? null : (float) $average;
    }

    public function render(): View
    {
        $products = $this->business->products()
            ->active()
            ->with(['category', 'images'])
            ->paginate(12);

        $reviews = $this->business->reviews()
            ->with(['user', 'product'])
            ->published()
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.storefront.business-profile', [
            'products' => $products,
            'reviews' => $reviews,
        ]);
    }
}
