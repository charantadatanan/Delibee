<x-storefront.layout :title="__('Privacy Policy')">
    <article class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center gap-3 lg:hidden">
            <a href="{{ auth()->check() ? route('settings.index') : route('marketplace') }}" class="ui-back size-9" aria-label="Go back" wire:navigate>
                <flux:icon name="chevron-left" class="size-5" />
            </a>
            <h1 class="font-display text-xl font-semibold">Privacy Policy</h1>
        </div>

        <header class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Legal</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Privacy Policy</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Last updated {{ now()->format('F Y') }}.</p>
        </header>

        <div class="ui-card bg-paper p-5 text-sm leading-relaxed text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400 sm:p-6">
            <h2 class="font-display text-xl font-semibold text-ink dark:text-zinc-100">What we collect</h2>
            <p class="mt-2">Your name, email address, phone number and delivery address when you order; the products you buy, the sellers you chat with, and the ratings you leave. We do not store card details because payments are not collected yet.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Why we use it</h2>
            <p class="mt-2">To place and deliver orders, connect you with the right seller, calculate Honey Points and BeeRank, prevent abuse, and keep the marketplace working. Sellers only see the delivery details needed to fulfil your order.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Your choices</h2>
            <p class="mt-2">You can update your profile in Settings, ask a seller a question in Bee Chat, or close your account. Some order and review records may be retained where the law or fraud prevention requires it.</p>

            <h2 class="mt-5 font-display text-xl font-semibold text-ink dark:text-zinc-100">Contact</h2>
            <p class="mt-2">Privacy questions can be raised through the Help Center or Bee Chat.</p>
        </div>
    </article>
</x-storefront.layout>
