<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper text-ink dark:bg-zinc-950 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" role="complementary" aria-label="Sidebar" class="border-e border-comb/80 bg-[#f6ecd4]/50 dark:border-zinc-800 dark:bg-zinc-900">
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
                    <flux:sidebar.item icon="shopping-bag" :href="route('seller.products')" :current="request()->routeIs('seller.products')" wire:navigate>
                        {{ __('Your products') }}
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

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-comb/70 bg-paper/90 backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-950/90">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="ms-2 flex items-center gap-2 font-display text-sm font-semibold" wire:navigate>
                <span class="clip-hex grid size-7 place-items-center bg-honey text-sm font-bold text-ink">d</span>
                deliBEE
            </a>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
