@php
    $user = auth()->user();
    $cartCount = $user ? (int) $user->cartItems()->sum('quantity') : 0;

    $items = [
        ['route' => 'categories.index', 'active' => 'categories.*', 'icon' => 'building-storefront', 'label' => 'Shop', 'y' => 8],
        ['route' => 'ranking', 'active' => 'ranking', 'icon' => 'trophy', 'label' => 'Rank', 'y' => 0],
        ['route' => 'cart.index', 'active' => 'cart.*', 'icon' => 'shopping-cart', 'label' => 'Cart', 'badge' => $cartCount, 'y' => 0],
        ['route' => $user ? 'dashboard' : 'login', 'active' => 'dashboard', 'icon' => 'user', 'label' => 'Profile', 'y' => 8],
        ['route' => $user ? 'profile.edit' : 'login', 'active' => 'profile.*', 'icon' => 'cog-6-tooth', 'label' => 'Settings', 'y' => 12],
    ];
@endphp

<nav aria-label="Mobile navigation" class="fixed inset-x-0 bottom-0 z-50 lg:hidden">
    <div class="relative mx-auto max-w-md px-3 pb-[max(8px,env(safe-area-inset-bottom))]">
        <div class="mobile-nav-bar absolute inset-x-3 bottom-0 h-14 rounded-t-[34px] border border-ink/10 bg-honey/95 shadow-[0_-10px_30px_rgba(30,30,30,0.14)] backdrop-blur dark:border-zinc-700 dark:bg-[#4a3c2f]/95"></div>

        <div class="mobile-nav-row relative flex items-end justify-between gap-1 px-3 pb-2 pt-3">
            @foreach (array_slice($items, 0, 2) as $index => $item)
                @include('components.storefront.mobile-nav-item', ['item' => $item, 'index' => $index])
            @endforeach

            <div class="mobile-nav-spacer w-20 shrink-0" aria-hidden="true"></div>

            @foreach (array_slice($items, 2) as $index => $item)
                @include('components.storefront.mobile-nav-item', ['item' => $item, 'index' => $index + 2])
            @endforeach
        </div>

        <button type="button"
                onclick="document.getElementById('mobile-hive-menu').showModal(); document.documentElement.classList.add('overflow-hidden')"
                class="mobile-hive group absolute bottom-14 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ink dark:focus-visible:outline-honey"
                aria-label="{{ __('Open menu') }}"
                aria-haspopup="dialog"
                aria-controls="mobile-hive-menu">
            <span class="grid size-16 place-items-center rounded-full border-4 border-paper bg-honey shadow-lg transition-transform group-hover:-translate-y-0.5 group-active:scale-95 dark:border-zinc-950 dark:bg-honey-deep">
                <img src="{{ asset('images/mobile/hive.png') }}"
                     alt=""
                     width="36"
                     height="36"
                     class="size-9 object-contain dark:brightness-0 dark:invert">
            </span>
        </button>
    </div>
</nav>

<x-storefront.mobile-menu />
