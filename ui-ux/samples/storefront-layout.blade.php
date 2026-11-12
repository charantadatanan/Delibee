@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-40 border-b border-comb/80 bg-paper/85 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/85">
            <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-3">
                <a href="{{ route('marketplace') }}" class="flex items-center gap-2.5" wire:navigate>
                    <span class="clip-hex grid size-9 place-items-center bg-honey font-display text-lg font-bold text-ink">d</span>
                    <span class="font-display text-xl font-semibold tracking-tight">deliBEE</span>
                </a>

                <nav aria-label="Primary" class="hidden items-center gap-1 text-sm sm:flex">
                    @foreach ([['marketplace', 'Marketplace'], ['categories.index', 'Categories'], ['ranking', 'Ranking']] as [$route, $label])
                        <a href="{{ route($route) }}"
                           class="rounded-lg px-3 py-2 transition {{ request()->routeIs($route) ? 'bg-ink font-medium text-paper dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 hover:bg-comb/50 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' }}"
                           wire:navigate>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="ms-auto flex items-center gap-2">
                    @auth
                        <a href="{{ route('cart.index') }}"
                           class="relative rounded-lg px-2.5 py-2 text-sm transition hover:bg-comb/50 dark:hover:bg-zinc-800"
                           wire:navigate>
                            <span aria-hidden="true">🛒</span>
                            <span class="sr-only">Cart</span>
                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -top-0.5 end-0 grid min-w-5 place-items-center rounded-full bg-honey px-1 text-xs font-semibold text-ink">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('rewards') }}"
                           class="hidden rounded-lg px-3 py-2 text-sm text-zinc-600 transition hover:bg-comb/50 hover:text-ink sm:inline dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                           wire:navigate>Rewards</a>
                        <a href="{{ route('orders.index') }}"
                           class="hidden rounded-lg px-3 py-2 text-sm text-zinc-600 transition hover:bg-comb/50 hover:text-ink sm:inline dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                           wire:navigate>Orders</a>
                        <a href="{{ route('dashboard') }}"
                           class="rounded-lg border border-ink/15 bg-white/60 px-3 py-1.5 text-sm font-medium transition hover:border-ink/30 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500"
                           wire:navigate>
                            {{ auth()->user()->name }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-comb/50 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                           wire:navigate>Sign in</a>
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
                           wire:navigate>Join deliBEE</a>
                    @endauth
                </div>
            </div>

            <div class="border-t border-comb/60 px-4 py-2 sm:hidden dark:border-zinc-800">
                <nav aria-label="Primary" class="flex items-center gap-1 text-sm">
                    @foreach ([['marketplace', 'Marketplace'], ['categories.index', 'Categories'], ['ranking', 'Ranking']] as [$route, $label])
                        <a href="{{ route($route) }}"
                           class="rounded-lg px-2.5 py-1.5 transition {{ request()->routeIs($route) ? 'bg-ink font-medium text-paper dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 hover:bg-comb/50 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                           wire:navigate>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
            {{ $slot }}
        </main>

        <footer class="mt-8 border-t border-comb/80 bg-ink text-paper/70 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="clip-hex grid size-8 place-items-center bg-honey font-display text-base font-bold text-ink">d</span>
                    <p class="text-sm text-paper dark:text-zinc-100">deliBEE</p>
                </div>

                <p class="max-w-md text-sm">
                    A marketplace that ranks sellers on how they are doing now, not how long they have been around.
                </p>

                <nav aria-label="Footer" class="flex gap-4 text-sm">
                    <a class="transition hover:text-honey" href="{{ route('marketplace') }}" wire:navigate>Marketplace</a>
                    <a class="transition hover:text-honey" href="{{ route('ranking') }}" wire:navigate>Ranking</a>
                </nav>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
