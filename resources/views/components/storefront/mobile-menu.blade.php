@php
    $user = auth()->user();
    $cartCount = $user ? (int) $user->cartItems()->sum('quantity') : 0;

    $groups = [
        [
            'title' => 'Shop',
            'links' => [
                ['label' => 'Marketplace', 'hint' => 'Everything in the hive', 'icon' => 'building-storefront', 'route' => 'marketplace'],
                ['label' => 'Categories', 'hint' => 'Browse by shelf', 'icon' => 'squares-2x2', 'route' => 'categories.index'],
                ['label' => 'BeeRank', 'hint' => 'Top sellers this month', 'icon' => 'trophy', 'route' => 'ranking'],
            ],
        ],
    ];

    if ($user) {
        $groups[] = [
            'title' => 'Your hive',
            'links' => [
                ['label' => 'Honey Pot', 'hint' => $cartCount > 0 ? $cartCount.' waiting for checkout' : 'Your basket', 'icon' => 'shopping-bag', 'route' => 'cart.index'],
                ['label' => 'Bee Flight', 'hint' => 'Track your orders', 'icon' => 'cube', 'route' => 'orders.index'],
                ['label' => 'Honey Rewards', 'hint' => 'Points, tiers and perks', 'icon' => 'star', 'route' => 'rewards'],
                ['label' => 'Bee Chat', 'hint' => 'Talk to a seller', 'icon' => 'chat-bubble-left-right', 'route' => 'chat.index'],
                ['label' => 'My Reviews', 'hint' => 'What you have tasted', 'icon' => 'pencil-square', 'route' => 'reviews.index'],
                ['label' => 'Profile', 'hint' => 'Your deliBEE account', 'icon' => 'user', 'route' => 'dashboard'],
                ['label' => 'Settings', 'hint' => 'Details and security', 'icon' => 'cog-6-tooth', 'route' => 'settings.index'],
            ],
        ];
    }

    $manage = [];

    if ($user?->hasRole('seller')) {
        $manage[] = ['label' => 'Seller desk', 'hint' => 'Products, orders and analytics', 'icon' => 'chart-bar', 'route' => 'seller.dashboard'];
    }

    if ($user?->isAdmin()) {
        $manage[] = ['label' => 'Hive Guard', 'hint' => 'Moderate the marketplace', 'icon' => 'shield-check', 'route' => 'admin.businesses'];
    }

    if (! $user?->hasRole('seller')) {
        $manage[] = ['label' => 'Sell on deliBEE', 'hint' => 'Open your own shop', 'icon' => 'plus-circle', 'route' => 'seller.onboarding'];
    }

    $manage[] = ['label' => 'Help', 'hint' => 'Questions and support', 'icon' => 'question-mark-circle', 'route' => 'help'];

    $groups[] = ['title' => $user ? 'Manage' : 'Grow with us', 'links' => $manage];
@endphp

<dialog id="mobile-hive-menu"
        aria-labelledby="mobile-hive-menu-title"
        class="mobile-hive-menu fixed inset-x-0 bottom-0 top-auto m-0 max-h-[85dvh] w-full max-w-md overflow-hidden rounded-t-[30px] border border-ink/15 bg-paper p-0 text-ink shadow-[0_-20px_60px_rgba(30,30,30,0.28)] dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 sm:mx-auto"
        onclick="if (event.target === this || event.target.closest('a[href], button[type=submit]')) { this.close(); document.documentElement.classList.remove('overflow-hidden'); }"
        onclose="document.documentElement.classList.remove('overflow-hidden')">
    <div class="flex max-h-[85dvh] flex-col">
        <div class="border-b border-ink/10 px-4 pb-3 pt-2 dark:border-zinc-800">
            <div class="mx-auto mb-2 h-1.5 w-9 rounded-full bg-ink/15 dark:bg-zinc-700" aria-hidden="true"></div>

            <div class="flex items-center justify-between gap-3">
                <x-ui.bee-logo class="h-auto w-24 shrink-0" />
                <h2 id="mobile-hive-menu-title" class="sr-only">{{ __('deliBEE menu') }}</h2>

                <button type="button"
                        onclick="this.closest('dialog').close()"
                        class="ui-back size-9 shrink-0"
                        aria-label="{{ __('Close menu') }}">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>
        </div>

        <div class="overflow-y-auto overscroll-contain px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3">
            @foreach ($groups as $group)
                @if (count($group['links']) > 0)
                    <section class="mt-4 first:mt-0">
                        <h3 class="px-1 text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">
                            {{ __($group['title']) }}
                        </h3>

                        <div class="mt-2 space-y-1.5">
                            @foreach ($group['links'] as $link)
                                <a href="{{ route($link['route']) }}"
                                   class="group flex min-h-14 items-center gap-3 rounded-2xl border border-ink/10 bg-white/75 px-3 py-2.5 transition hover:border-ink/30 hover:bg-white active:scale-[0.99] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600"
                                   wire:navigate>
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-honey/30 text-ink transition group-hover:bg-honey dark:bg-honey/15 dark:text-honey">
                                        <flux:icon :name="$link['icon']" class="size-[18px]" />
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ __($link['label']) }}</span>
                                        <span class="block truncate text-[11px] font-normal text-zinc-500 dark:text-zinc-400">{{ __($link['hint']) }}</span>
                                    </span>

                                    <flux:icon name="chevron-right" class="size-4 shrink-0 text-ink/30 dark:text-zinc-600" />
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <div class="mt-5 border-t border-ink/10 pt-4 dark:border-zinc-800">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-ink/20 px-4 text-sm font-semibold transition hover:border-ink/50 active:scale-[0.99] dark:border-zinc-700 dark:hover:border-zinc-500">
                            <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                            {{ __('Log out') }}
                        </button>
                    </form>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}"
                           class="flex min-h-12 items-center justify-center rounded-full border border-ink/25 px-4 text-sm font-semibold transition hover:border-ink/60 active:scale-[0.99] dark:border-zinc-700 dark:hover:border-zinc-500"
                           wire:navigate>
                            {{ __('Sign in') }}
                        </a>
                        <a href="{{ route('register') }}"
                           class="flex min-h-12 items-center justify-center rounded-full bg-ink px-4 text-sm font-semibold text-paper transition hover:bg-honey-deep active:scale-[0.99] dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey"
                           wire:navigate>
                            {{ __('Join deliBEE') }}
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</dialog>
