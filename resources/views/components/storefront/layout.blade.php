@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
        <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/90">
            <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3">
                <a href="{{ route('marketplace') }}" class="flex items-center gap-2 font-semibold" wire:navigate>
                    <span class="grid size-8 place-items-center rounded-full bg-amber-400 text-lg">🐝</span>
                    <span class="hidden sm:inline">deliBEE</span>
                </a>

                <nav class="flex items-center gap-1 text-sm">
                    <a href="{{ route('marketplace') }}"
                       class="rounded-lg px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('marketplace') ? 'font-semibold text-amber-600 dark:text-amber-400' : '' }}"
                       wire:navigate>
                        Marketplace
                    </a>
                    <a href="{{ route('categories.index') }}"
                       class="rounded-lg px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('categories.*') ? 'font-semibold text-amber-600 dark:text-amber-400' : '' }}"
                       wire:navigate>
                        Categories
                    </a>
                    <a href="{{ route('ranking') }}"
                       class="rounded-lg px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('ranking') ? 'font-semibold text-amber-600 dark:text-amber-400' : '' }}"
                       wire:navigate>
                        Ranking
                    </a>
                </nav>

                <div class="ms-auto flex items-center gap-2">
                    @auth
                        <a href="{{ route('cart.index') }}"
                           class="relative rounded-lg px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                           wire:navigate>
                            <span aria-hidden="true">🛒</span>
                            <span class="sr-only">Cart</span>
                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -top-0.5 end-0 grid min-w-5 place-items-center rounded-full bg-amber-500 px-1 text-xs font-semibold text-white">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('rewards') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            Rewards
                        </a>
                        <a href="{{ route('orders.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            Orders
                        </a>
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium dark:border-zinc-600" wire:navigate>
                            {{ auth()->user()->name }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            Sign in
                        </a>
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-600"
                           wire:navigate>
                            Join deliBEE
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700">
            <p>deliBEE — a marketplace that ranks businesses on how they are doing now.</p>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
