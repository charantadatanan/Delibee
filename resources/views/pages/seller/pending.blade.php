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
        <h1 class="text-2xl font-bold tracking-tight">Approval pending</h1>
        <p class="mt-1 text-sm text-zinc-500">
            Your shop is waiting for an admin to review it. You will be able to list products and take
            orders as soon as it is approved.
        </p>
    </div>

    @if ($this->business())
        <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $this->business()->name }}</p>
                    <p class="text-xs text-zinc-500">Submitted {{ $this->business()->created_at?->format('j M Y') }}</p>
                </div>

                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                    {{ $this->business()->status->label() }}
                </span>
            </div>

            @if ($this->business()->description)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $this->business()->description }}</p>
            @endif
        </section>
    @endif
</div>
