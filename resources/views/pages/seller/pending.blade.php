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

<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Approval pending</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Your shop is waiting for an admin to review it. You will be able to list products and take
            orders as soon as it is approved.
        </p>
    </div>

    @if ($this->business())
        <section class="rounded-2xl border border-comb/90 bg-white/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/70">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $this->business()->name }}</p>
                    <p class="text-xs text-zinc-500">Submitted {{ $this->business()->created_at?->format('j M Y') }}</p>
                </div>

                <span class="rounded-full bg-honey/15 px-3 py-1 text-xs font-semibold text-honey-deep dark:bg-honey/15 dark:text-honey">
                    {{ $this->business()->status->label() }}
                </span>
            </div>

            @if ($this->business()->description)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $this->business()->description }}</p>
            @endif
        </section>
    @endif
</div>
