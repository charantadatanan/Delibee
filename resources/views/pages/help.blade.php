<x-storefront.layout :title="__('Need Help')">
    <div class="mx-auto max-w-2xl space-y-5">
        <div class="flex items-center gap-3 lg:hidden">
            <a href="{{ route('marketplace') }}" class="ui-back size-9" aria-label="Back to marketplace" wire:navigate>
                <flux:icon name="chevron-left" class="size-5" />
            </a>
            <h1 class="font-display text-xl font-semibold">Need Help?</h1>
        </div>

        <div class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Support</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Need Help?</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Bee Chat is the fastest way to reach a seller or the hive team.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ auth()->check() ? route('chat.index') : route('login') }}" class="ui-card bg-paper p-5 text-center transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900" wire:navigate>
                <span class="mx-auto grid size-14 place-items-center rounded-full border border-ink/50 bg-honey">
                    <flux:icon name="chat-bubble-left-right" class="size-7 text-ink" />
                </span>
                <p class="mt-3 font-display text-xl font-semibold">Chat Support</p>
                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Message a seller or open a hive conversation.</p>
            </a>

            <a href="{{ auth()->check() ? route('orders.index') : route('login') }}" class="ui-card bg-paper p-5 text-center transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900" wire:navigate>
                <span class="mx-auto grid size-14 place-items-center rounded-full border border-ink/50 bg-honey">
                    <flux:icon name="cube" class="size-7 text-ink" />
                </span>
                <p class="mt-3 font-display text-xl font-semibold">Order Help</p>
                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Check a delivery, cancel an order or review an item.</p>
            </a>
        </div>

        <section class="ui-card-honey p-4 sm:p-5">
            <h2 class="font-display text-xl font-semibold">Report a delivery issue</h2>
            <p class="mt-1 text-sm text-zinc-700">
                Damaged, missing or late? Open the order, then message the seller from the order page. The
                conversation stays attached to that order so the hive can follow it up.
            </p>

            <ul class="mt-4 space-y-2 text-sm">
                <li class="flex items-start gap-2">
                    <span class="mt-1 size-2 shrink-0 rounded-full bg-ink" aria-hidden="true"></span>
                    <span><strong>Damaged item:</strong> keep the packaging and add a photo in Bee Chat if you can.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 size-2 shrink-0 rounded-full bg-ink" aria-hidden="true"></span>
                    <span><strong>Missing item:</strong> check the order items first, then message the seller.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 size-2 shrink-0 rounded-full bg-ink" aria-hidden="true"></span>
                    <span><strong>Late delivery:</strong> the order timeline shows the latest status and estimate.</span>
                </li>
            </ul>

            <a href="{{ auth()->check() ? route('orders.index') : route('login') }}" class="ui-btn ui-btn-sm mt-4" wire:navigate>
                Go to my orders
            </a>
        </section>

        <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
            <h2 class="font-display text-xl font-semibold">Common questions</h2>

            <div class="mt-3 space-y-2">
                <details class="rounded-xl border border-ink/20 bg-white/60 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <summary class="cursor-pointer text-sm font-semibold">Why is my basket split into several orders?</summary>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Each seller packs and ships their own items, so checkout creates one order per shop. You still pay one basket total.</p>
                </details>
                <details class="rounded-xl border border-ink/20 bg-white/60 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <summary class="cursor-pointer text-sm font-semibold">When can I leave a rating?</summary>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">As soon as the order is marked delivered. Ratings are tied to a real order item, which keeps reviews verified.</p>
                </details>
                <details class="rounded-xl border border-ink/20 bg-white/60 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <summary class="cursor-pointer text-sm font-semibold">Is payment collected now?</summary>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">No. Payments are on hold while the gateway is connected. Orders are recorded and sellers are paid directly once payments go live.</p>
                </details>
            </div>
        </section>
    </div>
</x-storefront.layout>
