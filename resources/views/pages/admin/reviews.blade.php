<?php

use App\Enums\ReviewStatus;
use App\Models\Review;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Hive Guard — Reviews')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Computed]
    public function reviews()
    {
        return Review::query()
            ->with(['user', 'business', 'product'])
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->latest('created_at')
            ->paginate(20);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function hide(int $reviewId): void
    {
        $review = $this->findReview($reviewId);
        Gate::authorize('moderate', $review);

        if ($review->status === ReviewStatus::Hidden) {
            return;
        }

        $review->update(['status' => ReviewStatus::Hidden]);

        Flux::toast('Review hidden from the storefront and BeeRank.', variant: 'success');

        unset($this->reviews);
    }

    public function publish(int $reviewId): void
    {
        $review = $this->findReview($reviewId);
        Gate::authorize('moderate', $review);

        if ($review->status === ReviewStatus::Published) {
            return;
        }

        $review->update(['status' => ReviewStatus::Published]);

        Flux::toast('Review published.', variant: 'success');

        unset($this->reviews);
    }

    private function findReview(int $reviewId): Review
    {
        return Review::query()->findOrFail($reviewId);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Hive Guard</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                Moderate ratings before they influence BeeRank. Hidden reviews stay on record but no longer count.
            </p>
        </div>

        <label class="block">
            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Status</span>
            <select wire:model.live="statusFilter"
                    class="mt-1.5 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                <option value="all">All statuses</option>
                <option value="pending">Awaiting moderation</option>
                <option value="published">Published</option>
                <option value="hidden">Hidden</option>
            </select>
        </label>
    </div>

    @if ($this->reviews()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">No reviews match this filter.</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Try another status.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-comb/90 dark:border-zinc-800">
            <ul class="divide-y divide-comb/70 dark:divide-zinc-800">
                @foreach ($this->reviews() as $review)
                    @php($statusClasses = match ($review->status) {
                        ReviewStatus::Published => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
                        ReviewStatus::Pending => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
                        ReviewStatus::Hidden => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                    })

                    <li class="bg-white/70 p-4 dark:bg-zinc-900/70">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-display text-lg font-semibold">{{ $review->business?->name ?? 'Unknown business' }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                        {{ $review->status->label() }}
                                    </span>
                                    @if ($review->isVerifiedPurchase())
                                        <span class="rounded-full bg-leaf/10 px-2.5 py-1 text-xs font-medium text-leaf dark:bg-leaf/20 dark:text-emerald-300">
                                            Verified purchase
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $review->user?->name ?? 'Unknown customer' }}
                                    @if ($review->product)
                                        on {{ $review->product->name }}
                                    @endif
                                    · {{ $review->created_at?->format('j M Y') }}
                                </p>

                                <p class="mt-2 text-sm font-medium">
                                    <span class="sr-only">{{ $review->rating }} out of 5 stars</span>
                                    <span aria-hidden="true">{{ str_repeat('★', $review->rating) }}</span><span class="text-zinc-400 dark:text-zinc-600" aria-hidden="true">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </p>

                                @if ($review->body)
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $review->body }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($review->status !== ReviewStatus::Hidden)
                                    <button type="button"
                                            wire:click="hide({{ $review->id }})"
                                            wire:confirm="Hide this review from the storefront and BeeRank?"
                                            class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-500/10">
                                        Hide
                                    </button>
                                @endif

                                @if ($review->status !== ReviewStatus::Published)
                                    <button type="button"
                                            wire:click="publish({{ $review->id }})"
                                            class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                                        Publish
                                    </button>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6">{{ $this->reviews()->links() }}</div>
    @endif
</div>
