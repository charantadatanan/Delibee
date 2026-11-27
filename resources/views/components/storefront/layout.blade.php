@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <x-storefront.mobile-header />

        <header class="sticky top-0 z-40 hidden border-b border-ink/10 bg-paper/90 backdrop-blur-xl lg:block dark:border-zinc-800 dark:bg-zinc-950/90">
            <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-3">
                <a href="{{ route('marketplace') }}" class="flex shrink-0 items-center" aria-label="deliBEE marketplace" wire:navigate>
                    <x-ui.bee-logo class="h-auto w-32" />
                </a>

                <nav aria-label="Primary" class="flex items-center gap-1 text-sm">
                    @foreach ([['marketplace', 'Marketplace'], ['categories.index', 'Categories'], ['ranking', 'BeeRank']] as [$route, $label])
                        <a href="{{ route($route) }}"
                           class="rounded-full px-3.5 py-2 transition {{ request()->routeIs($route) ? 'border border-ink bg-ink font-semibold text-paper dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border border-transparent text-zinc-600 hover:border-ink/20 hover:bg-white/70 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100' }}"
                           wire:navigate>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="ms-auto flex items-center gap-2">
                    <x-ui.theme-toggle />

                    @auth
                        <a href="{{ route('cart.index') }}"
                           class="relative grid size-10 place-items-center rounded-full border border-ink/30 bg-white/70 transition hover:border-ink hover:bg-honey dark:border-zinc-700 dark:bg-zinc-900"
                           aria-label="Honey Pot cart"
                           wire:navigate>
                            <flux:icon name="shopping-bag" class="size-5" />
                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -end-1 -top-1 grid min-w-5 place-items-center rounded-full border border-ink bg-honey px-1 text-[10px] font-bold text-ink">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('orders.index') }}"
                           class="hidden rounded-full px-3 py-2 text-sm text-zinc-600 transition hover:bg-white/70 hover:text-ink xl:inline dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100"
                           wire:navigate>Orders</a>
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-ink/30 bg-white/70 py-1 ps-1 pe-3 text-sm font-medium transition hover:border-ink dark:border-zinc-700 dark:bg-zinc-900"
                           wire:navigate>
                            <span class="grid size-7 place-items-center rounded-full bg-honey font-display text-[11px] font-bold text-ink">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span class="max-w-24 truncate">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-full px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-white/70 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100"
                           wire:navigate>Sign in</a>
                        <a href="{{ route('register') }}"
                           class="ui-btn ui-btn-sm"
                           wire:navigate>Join deliBEE</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 pt-3 pb-10 sm:pt-4 lg:pt-10 lg:pb-10">
            {{ $slot }}
        </main>

        <footer class="mt-10 border-t border-ink/10 px-4 pb-32 pt-8 lg:hidden dark:border-zinc-800">
            <div class="flex items-center justify-between gap-4">
                <x-ui.bee-logo class="h-auto w-24" />
                <a href="{{ route('seller.onboarding') }}"
                   class="inline-flex min-h-11 items-center text-xs font-semibold text-honey-deep dark:text-honey"
                   wire:navigate>
                    Sell on deliBEE →
                </a>
            </div>

            <nav aria-label="Footer" class="mt-3 grid grid-cols-2 gap-x-4 text-sm">
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('marketplace') }}" wire:navigate>Marketplace</a>
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('categories.index') }}" wire:navigate>Categories</a>
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('ranking') }}" wire:navigate>BeeRank leaderboard</a>
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('help') }}" wire:navigate>Help</a>
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('credits') }}" wire:navigate>Demo photo credits</a>
                <a class="inline-flex min-h-11 items-center text-zinc-600 dark:text-zinc-300"
                   href="{{ route('legal.terms') }}" wire:navigate>Terms</a>
            </nav>

            <p class="mt-2 text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                © {{ now()->year }} deliBEE. Ranked on the last 30 days of trading.
            </p>
        </footer>

        <footer class="mt-16 hidden overflow-hidden bg-ink text-paper lg:block dark:border-t dark:border-zinc-800">
            <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-[1.4fr_1fr_1fr]">
                <div class="absolute inset-0 bg-comb opacity-[0.07]"></div>

                <div class="relative max-w-md">
                    <x-ui.bee-logo class="h-auto w-36 brightness-0 invert" />
                    <p class="mt-4 text-sm leading-relaxed text-paper/70">
                        A marketplace that ranks sellers on how they are doing now, not how long they have been around.
                        One basket, many local businesses.
                    </p>
                    <p class="mt-5 inline-flex items-center gap-2 rounded-full border border-paper/20 px-3 py-1 text-xs text-paper/70">
                        <span class="size-1.5 rounded-full bg-honey" aria-hidden="true"></span>
                        shop. support. grow together.
                    </p>
                </div>

                <nav aria-label="Shop" class="relative text-sm">
                    <p class="font-display text-base font-semibold text-paper">Shop</p>
                    <ul class="mt-4 space-y-2.5 text-paper/70">
                        <li><a class="transition hover:text-honey" href="{{ route('marketplace') }}" wire:navigate>Marketplace</a></li>
                        <li><a class="transition hover:text-honey" href="{{ route('categories.index') }}" wire:navigate>Categories</a></li>
                        <li><a class="transition hover:text-honey" href="{{ route('ranking') }}" wire:navigate>BeeRank leaderboard</a></li>
                    </ul>
                </nav>

                <nav aria-label="Hive" class="relative text-sm">
                    <p class="font-display text-base font-semibold text-paper">The hive</p>
                    <ul class="mt-4 space-y-2.5 text-paper/70">
                        @auth
                            <li><a class="transition hover:text-honey" href="{{ route('orders.index') }}" wire:navigate>Your orders</a></li>
                            <li><a class="transition hover:text-honey" href="{{ route('rewards') }}" wire:navigate>Honey Rewards</a></li>
                            <li><a class="transition hover:text-honey" href="{{ route('chat.index') }}" wire:navigate>Bee Chat</a></li>
                        @else
                            <li><a class="transition hover:text-honey" href="{{ route('login') }}" wire:navigate>Sign in</a></li>
                            <li><a class="transition hover:text-honey" href="{{ route('register') }}" wire:navigate>Join deliBEE</a></li>
                        @endauth
                        <li><a class="transition hover:text-honey" href="{{ route('seller.onboarding') }}" wire:navigate>Sell on deliBEE</a></li>
                    </ul>
                </nav>
            </div>

            <div class="relative border-t border-paper/10">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-5 text-xs text-paper/50">
                    <p>© {{ now()->year }} deliBEE. Made for local.</p>
                    <p>Ranked on the last 30 days of trading.</p>
                    <a href="{{ route('credits') }}" class="underline-offset-4 hover:text-honey hover:underline" wire:navigate>Demo photo credits</a>
                </div>
            </div>
        </footer>

        <x-storefront.mobile-nav />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
