<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper text-ink dark:bg-zinc-950 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" role="complementary" aria-label="Sidebar" class="border-e border-comb/80 bg-[#FFF4C5] dark:border-zinc-800 dark:bg-zinc-900 lg:bg-[#FFF4C5]/50">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav aria-label="Platform">
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if (auth()->check())
                        <flux:sidebar.item icon="shopping-bag" :href="route('cart.index')" :current="request()->routeIs('cart.index')" wire:navigate>
                            {{ __('Honey Pot') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cube" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>
                            {{ __('Bee Flight') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="star" :href="route('rewards')" :current="request()->routeIs('rewards')" wire:navigate>
                            {{ __('Honey Rewards') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('chat.index')" :current="request()->routeIs('chat.*')" wire:navigate>
                            {{ __('Bee Chat') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            @if (auth()->check() && auth()->user()->hasRole('seller'))
            <flux:sidebar.nav aria-label="Seller">
                <flux:sidebar.group :heading="__('Seller')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('seller.dashboard')" :current="request()->routeIs('seller.dashboard')" wire:navigate>
                        {{ __('Overview') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar" :href="route('seller.analytics')" :current="request()->routeIs('seller.analytics')" wire:navigate>
                        {{ __('Analytics') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="archive-box" :href="route('seller.products')" :current="request()->routeIs('seller.products')" wire:navigate>
                        {{ __('Inventory') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cube" :href="route('seller.orders')" :current="request()->routeIs('seller.orders')" wire:navigate>
                        {{ __('Orders') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
            @endif

            @if (auth()->check() && auth()->user()->isAdmin())
            <flux:sidebar.nav aria-label="Hive Guard">
                <flux:sidebar.group :heading="__('Hive Guard')" class="grid">
                    <flux:sidebar.item icon="shield-check" :href="route('admin.businesses')" :current="request()->routeIs('admin.businesses')" wire:navigate>
                        {{ __('Businesses') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cube" :href="route('admin.orders')" :current="request()->routeIs('admin.orders')" wire:navigate>
                        {{ __('Orders') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="star" :href="route('admin.reviews')" :current="request()->routeIs('admin.reviews')" wire:navigate>
                        {{ __('Reviews') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
            @endif

            <flux:spacer />

            <flux:sidebar.nav aria-label="Marketplace">
                <flux:sidebar.item icon="building-storefront" :href="route('marketplace')" wire:navigate>
                    {{ __('Marketplace') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="trophy" :href="route('ranking')" wire:navigate>
                    {{ __('BeeRank') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="flex items-center justify-between gap-3 rounded-xl border border-comb/70 bg-paper/70 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900/70">
                <span class="text-sm font-medium text-ink dark:text-zinc-200">{{ __('Theme') }}</span>
                <x-ui.theme-toggle />
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        @if (request()->routeIs('admin.*'))
            <!-- Admin-only mobile app bar; customer and seller pages carry their own header. -->
            <header data-mobile-app-bar class="sticky top-0 z-40 grid grid-cols-[2.5rem_1fr_auto] items-center gap-2 border-b border-ink/10 bg-paper/95 px-4 py-2.5 backdrop-blur-md lg:hidden dark:border-zinc-800 dark:bg-zinc-950/95">
                <button type="button"
                        x-data
                        x-on:click="$dispatch('flux-sidebar-toggle')"
                        data-flux-sidebar-toggle
                        class="grid size-10 place-items-center justify-self-start rounded-full border border-ink/50 bg-honey transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink active:scale-95 dark:border-zinc-700 dark:bg-honey-deep dark:focus-visible:outline-honey"
                        aria-label="{{ __('Open menu') }}">
                    <img src="{{ asset('images/mobile/hive.png') }}"
                         alt=""
                         width="22"
                         height="22"
                         class="size-[22px] object-contain dark:brightness-0 dark:invert">
                </button>

                <a href="{{ route('dashboard') }}" class="mx-auto flex items-center justify-center" aria-label="deliBEE dashboard" wire:navigate>
                    <x-ui.bee-logo class="h-auto w-24" />
                </a>

                <div class="flex items-center justify-end gap-2">
                    <x-ui.theme-toggle />

                    <a href="{{ route('dashboard') }}"
                       class="grid size-9 place-items-center rounded-full border border-ink/60 bg-honey font-display text-xs font-bold text-ink dark:border-zinc-700"
                       aria-label="Your profile"
                       wire:navigate>
                        {{ auth()->user()->initials() }}
                    </a>
                </div>
            </header>
        @endif

        {{ $slot }}

        @auth
            @if (auth()->user()->isAdmin() && request()->routeIs('admin.*'))
                <x-ui.admin-mobile-nav />
            @elseif (auth()->user()->hasRole('seller') && request()->routeIs('seller.*') && ! request()->routeIs('seller.onboarding', 'seller.pending'))
                <x-ui.seller-mobile-nav />
            @else
                <x-storefront.mobile-nav />
            @endif
        @endauth

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
