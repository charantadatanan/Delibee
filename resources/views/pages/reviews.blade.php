<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('My Reviews')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function reviews()
    {
        return auth()->user()
            ->reviews()
            ->with(['business', 'product'])
            ->latest('created_at')
            ->paginate(10);
    }
};
?>

<div class="space-y-5">
    <x-ui.mobile-header :title="__('My Reviews')" :back="route('dashboard')" />

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Honey rating</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">My Reviews</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Every rating you have left after a delivered order. Ratings can only come from real purchases.
        </p>
    </div>

    @if ($this->reviews()->isEmpty())
        <x-ui.empty-state
            title="No reviews yet"
            description="After a delivered order, rate the item and your review will appear here."
            icon="star">
            <a href="{{ route('orders.index') }}" class="ui-btn" wire:navigate>View your orders</a>
        </x-ui.empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($this->reviews() as $review)
                <li class="ui-card bg-paper p-4 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full border border-ink/50 bg-honey font-display text-sm font-bold text-ink">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->business?->name ?? 'd', 0, 1)) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold">{{ $review->business?->name ?? 'deliBEE seller' }}</p>

                                @if ($review->isVerifiedPurchase())
                                    <span class="rounded-full border border-leaf/40 bg-leaf/10 px-2 py-0.5 text-[10px] font-medium text-leaf dark:text-emerald-300">
                                        Verified Buyer
                                    </span>
                                @endif

                                <span class="ms-auto text-[10px] text-zinc-500 dark:text-zinc-400">{{ $review->created_at?->diffForHumans() }}</span>
                            </div>

                            <p class="mt-0.5 text-sm text-honey-deep">
                                {{ str_repeat('★', $review->rating) }}<span class="text-comb dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </p>

                            @if ($review->product)
                                <a href="{{ route('products.show', [$review->business?->slug, $review->product->slug]) }}"
                                   class="mt-1 inline-block text-xs font-medium text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-400"
                                   wire:navigate>
                                    {{ $review->product->name }}
                                </a>
                            @endif

                            @if ($review->title)
                                <p class="mt-2 text-sm font-semibold">{{ $review->title }}</p>
                            @endif

                            @if ($review->body)
                                <p class="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $review->body }}</p>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-5">{{ $this->reviews()->links() }}</div>
    @endif
</div>
