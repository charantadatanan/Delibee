<x-storefront.layout :title="__('Terms & Conditions')">
    <article class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center gap-3 lg:hidden">
            <a href="{{ auth()->check() ? route('settings.index') : route('marketplace') }}" class="ui-back size-9" aria-label="Go back" wire:navigate>
                <flux:icon name="chevron-left" class="size-5" />
            </a>
            <h1 class="font-display text-xl font-semibold">Terms & Conditions</h1>
        </div>

        <header class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Legal</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Terms & Conditions</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Last updated {{ now()->format('F Y') }}.</p>
        </header>

        <div class="ui-card bg-paper p-5 text-sm leading-relaxed text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400 sm:p-6">
            <h2 class="font-display text-xl font-semibold text-ink dark:text-zinc-100">Using deliBEE</h2>
            <p class="mt-2">deliBEE is a marketplace that connects local businesses with customers. Each seller is responsible for the products they list, the stock they hold, and the orders they fulfil.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Orders and payments</h2>
            <p class="mt-2">A basket that spans several sellers becomes one order per seller. Payment is currently on hold while the payment gateway is connected; orders are recorded but no money is collected through deliBEE yet.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Ratings</h2>
            <p class="mt-2">Only customers with a delivered order can rate an item. Reviews are tied to that purchase so the BeeRank reflects real trading, not anonymous feedback.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Closing an account</h2>
            <p class="mt-2">You can close your account from Settings. Orders and reviews that are required for tax, fraud prevention or seller records may be retained as described in the privacy policy.</p>
        </div>
    </article>
</x-storefront.layout>
