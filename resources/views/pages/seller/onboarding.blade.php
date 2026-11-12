<?php

use App\Enums\BusinessStatus;
use App\Models\Business;
use Illuminate\Support\Str;
use Livewire\Component;

new #[Livewire\Attributes\Title('Open your shop')] class extends Component
{
    public string $name = '';

    public string $description = '';

    public string $currency = 'PHP';

    public function mount(): void
    {
        if (auth()->user()->sellerBusiness() !== null) {
            $this->redirect(route('seller.products'), navigate: true);

            return;
        }

        $this->currency = (string) config('commerce.currency', 'PHP');
    }

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $user = auth()->user();

        if ($user->sellerBusiness() !== null) {
            $this->redirect(route('seller.products'), navigate: true);

            return;
        }

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $this->name,
            'slug' => $this->uniqueSlug(),
            'description' => $this->description !== '' ? $this->description : null,
            'currency' => strtoupper($this->currency),
            'status' => BusinessStatus::Pending,
        ]);

        if (! $user->hasRole('seller')) {
            $user->assignRole('seller');
        }

        \Flux\Flux::toast('Your shop has been submitted for approval.', variant: 'success');

        $this->redirect(route('seller.pending'), navigate: true);
    }

    private function uniqueSlug(): string
    {
        $base = Str::slug($this->name) ?: 'shop';

        do {
            $slug = $base.'-'.Str::lower(Str::random(4));
        } while (Business::where('slug', $slug)->exists());

        return $slug;
    }
};
?>

<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Open your shop</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Tell us about your business. Once an admin approves it you can list products and start
            fulfilling orders.
        </p>
    </div>

    <form wire:submit="submit" class="space-y-5 rounded-2xl border border-comb/90 bg-white/70 p-6 dark:border-zinc-800 dark:bg-zinc-900/70">
        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Business name</span>
            <input type="text" wire:model="name" placeholder="Sunrise Orchard"
                   class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">What do you sell?</span>
            <textarea wire:model="description" rows="4" placeholder="Freshly picked fruit, delivered within the day."
                      class="mt-1.5 w-full rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950"></textarea>
        </label>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Currency</span>
            <input type="text" wire:model="currency" maxlength="3"
                   class="mt-1.5 w-24 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm uppercase focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
        </label>

        <button type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-ink px-4 py-2.5 font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
            <span wire:loading.remove wire:target="submit">Submit for approval</span>
            <span wire:loading wire:target="submit">Submitting…</span>
        </button>
    </form>
</div>
