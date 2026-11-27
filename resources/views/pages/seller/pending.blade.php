<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Approval pending')] class extends Component
{
    #[Computed]
    public function business()
    {
        return auth()->user()->sellerBusiness();
    }
};
?>

<div class="mx-auto max-w-2xl space-y-5">
    <x-ui.mobile-header :title="__('Approval pending')" :back="route('dashboard')" />

    <section class="relative overflow-hidden rounded-[28px] border border-ink/70 bg-hero p-6 text-center dark:border-zinc-800 dark:bg-zinc-900">
        <div class="absolute inset-0 bg-comb opacity-40 dark:opacity-10"></div>

        <div class="relative">
            <span class="mx-auto grid size-20 place-items-center rounded-full border-2 border-ink bg-honey shadow-[0_4px_0_0_var(--color-ink)] dark:border-zinc-700">
                <flux:icon name="clock" class="size-9 text-ink" />
            </span>

            <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight">Approval pending</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-zinc-600 dark:text-zinc-400">
                Your shop is in the review queue. You can list products and take orders as soon as an admin approves it.
            </p>

            <ol class="mx-auto mt-5 grid max-w-sm grid-cols-3 gap-2 text-left" aria-label="Seller sign-up progress">
                <li class="rounded-xl border border-ink/20 bg-white/60 p-2 text-[10px] dark:bg-zinc-950">
                    <p class="font-bold uppercase tracking-wide text-zinc-500">Step 1</p>
                    <p class="font-semibold">Details</p>
                </li>
                <li class="rounded-xl border border-ink bg-honey p-2 text-[10px]">
                    <p class="font-bold uppercase tracking-wide">Step 2</p>
                    <p class="font-semibold">Review</p>
                </li>
                <li class="rounded-xl border border-ink/20 bg-white/60 p-2 text-[10px] text-zinc-500 dark:bg-zinc-950">
                    <p class="font-bold uppercase tracking-wide">Step 3</p>
                    <p class="font-semibold">Go live</p>
                </li>
            </ol>
        </div>
    </section>

    @if ($this->business())
        <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-display text-lg font-semibold">{{ $this->business()->name }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Submitted {{ $this->business()->created_at?->format('j M Y') }}</p>
                </div>

                <span class="rounded-full border border-[#D9AE55] bg-[#FFF1CE] px-3 py-1 text-[10px] font-semibold text-[#6A4700]">
                    {{ $this->business()->status->label() }}
                </span>
            </div>

            @if ($this->business()->description)
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $this->business()->description }}</p>
            @endif
        </section>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('marketplace') }}" class="ui-btn flex-1" wire:navigate>Browse the marketplace</a>
        <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-ghost flex-1" wire:navigate>Back to profile</a>
    </div>
</div>
