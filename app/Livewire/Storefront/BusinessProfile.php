<?php

namespace App\Livewire\Storefront;

use App\Models\Business;
use App\Models\Conversation;
use App\Services\BeeRankService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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

    #[Computed]
    public function reviewCount(): int
    {
        return $this->business->reviews()->published()->count();
    }

    /**
     * @return array<int, array{star: int, count: int, percent: int}>
     */
    #[Computed]
    public function ratingBreakdown(): array
    {
        $counts = $this->business->reviews()
            ->published()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $total = (int) $counts->sum();

        return collect(range(5, 1))
            ->map(fn (int $star): array => [
                'star' => $star,
                'count' => (int) ($counts[$star] ?? 0),
                'percent' => $total > 0 ? (int) round(((int) ($counts[$star] ?? 0) / $total) * 100) : 0,
            ])
            ->all();
    }

    public function startChat(): void
    {
        $user = Auth::user();

        if ($user === null) {
            Flux::toast('Please sign in to message a seller.', variant: 'info');

            $this->redirect(route('login'), navigate: true);

            return;
        }

        if ($user->id === $this->business->owner_id) {
            return;
        }

        $conversation = Conversation::firstOrCreate([
            'business_id' => $this->business->id,
            'user_id' => $user->id,
        ]);

        $this->redirect(route('chat.show', $conversation), navigate: true);
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
            'productCategories' => $products->getCollection()
                ->pluck('category')
                ->filter()
                ->unique('id')
                ->values(),
        ]);
    }
}
