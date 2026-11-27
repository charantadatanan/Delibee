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

        Business::create([
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

<div class="mx-auto max-w-2xl space-y-5">
    <x-ui.mobile-header :title="__('Open your shop')" :back="route('dashboard')" />

    <div class="hidden lg:block">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Sell on deliBEE</p>
        <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Open your shop</h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Tell us about your business. Once an admin approves it, you can list products and fulfil orders.
        </p>
    </div>

    <ol class="grid grid-cols-3 gap-2" aria-label="Seller sign-up steps">
        <li class="rounded-xl border border-ink bg-honey px-3 py-2 text-ink">
            <p class="text-[9px] font-bold uppercase tracking-wide">Step 1</p>
            <p class="text-xs font-semibold">Store details</p>
        </li>
        <li class="rounded-xl border border-ink/20 bg-white/60 px-3 py-2 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
            <p class="text-[9px] font-bold uppercase tracking-wide">Step 2</p>
            <p class="text-xs font-semibold">Branding</p>
        </li>
        <li class="rounded-xl border border-ink/20 bg-white/60 px-3 py-2 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
            <p class="text-[9px] font-bold uppercase tracking-wide">Step 3</p>
            <p class="text-xs font-semibold">Review</p>
        </li>
    </ol>

    <form wire:submit="submit" class="ui-card bg-paper p-4 dark:bg-zinc-900 sm:p-6">
        <div class="flex items-center gap-2">
            <span class="grid size-9 place-items-center rounded-full border border-ink/50 bg-honey font-display text-xs font-bold">1</span>
            <div>
                <h2 class="font-display text-xl font-semibold">Store details</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">You can add a logo, banner and category after approval.</p>
            </div>
        </div>

        <div class="mt-5 space-y-4">
            <label class="block">
                <span class="ui-label">Business name</span>
                <input type="text" wire:model="name" placeholder="Sunrise Orchard" class="ui-field mt-1.5">
                @error('name') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="ui-label">What do you sell?</span>
                <textarea wire:model="description" rows="4" placeholder="Freshly picked fruit, delivered within the day." class="ui-field mt-1.5"></textarea>
                @error('description') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </label>

            <label class="block max-w-40">
                <span class="ui-label">Currency</span>
                <input type="text" wire:model="currency" maxlength="3" class="ui-field mt-1.5 uppercase">
                @error('currency') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </label>
        </div>

        <div class="ui-note mt-4">
            <p class="font-medium text-ink dark:text-zinc-200">What happens next?</p>
            <p class="mt-1 text-xs">
                An admin reviews the shop. After approval you can add products and start taking orders. KYC documents
                and payout details are collected then; payments are currently on hold.
            </p>
        </div>

        <button type="submit" wire:loading.attr="disabled" class="ui-btn mt-4 w-full">
            <span wire:loading.remove wire:target="submit">Submit for approval</span>
            <span wire:loading wire:target="submit">Submitting…</span>
        </button>

        <p class="mt-3 text-center text-[10px] text-zinc-500 dark:text-zinc-400">
            By signing up you agree to the deliBEE terms and privacy policy.
        </p>
    </form>

    <div class="lg:hidden">
        <x-ui.hills class="h-24 w-full" />
    </div>
</div>
