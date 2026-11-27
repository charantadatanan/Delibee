@php
    $user = auth()->user();
    $cartCount = $user ? (int) $user->cartItems()->sum('quantity') : 0;

    $items = [
        ['route' => 'categories.index', 'active' => 'categories.*', 'icon' => 'building-storefront', 'label' => 'Shop'],
        ['route' => 'ranking', 'active' => 'ranking', 'icon' => 'trophy', 'label' => 'Rank'],
        ['route' => 'cart.index', 'active' => 'cart.*', 'icon' => 'shopping-cart', 'label' => 'Cart', 'badge' => $cartCount],
        ['route' => $user ? 'dashboard' : 'login', 'active' => 'dashboard', 'icon' => 'user', 'label' => 'Profile'],
        ['route' => $user ? 'profile.edit' : 'login', 'active' => 'profile.*', 'icon' => 'cog-6-tooth', 'label' => 'Settings'],
    ];

    // Arc positions, left to right: 180°, 135°, 90°, 45°, 0°.
    $radial = [
        [-1, 0],
        [-0.7071, 0.7071],
        [0, 1],
        [0.7071, 0.7071],
        [1, 0],
    ];
@endphp

<nav aria-label="Mobile navigation"
     x-data="{ open: false }"
     x-bind:class="{ 'is-open': open }"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
     x-on:keydown.escape.window="open = false"
     class="mobile-radial-nav fixed inset-x-0 bottom-0 z-50 lg:hidden">
    <div class="relative mx-auto max-w-md px-3 pb-[max(8px,env(safe-area-inset-bottom))]">
        <div x-show="open"
             x-transition.opacity.duration.200ms
             x-cloak
             x-on:click="open = false; document.documentElement.classList.remove('overflow-hidden')"
             class="mobile-radial-scrim fixed inset-0 touch-none bg-ink/30 backdrop-blur-[2px]"
             aria-hidden="true"></div>

        <button type="button"
                x-on:click="open = !open"
                x-bind:aria-expanded="open.toString()"
                x-bind:aria-label="open ? '{{ __('Hide navigation') }}' : '{{ __('Show navigation') }}'"
                class="mobile-hive group absolute left-1/2 z-20 flex -translate-x-1/2 flex-col items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ink dark:focus-visible:outline-honey"
                aria-controls="mobile-radial-items">
            <span class="grid size-16 place-items-center rounded-full border-4 border-paper bg-honey shadow-lg transition-transform group-hover:-translate-y-0.5 group-active:scale-95 dark:border-zinc-950 dark:bg-honey-deep">
                <img src="{{ asset('images/mobile/hive.png') }}"
                     alt=""
                     width="36"
                     height="36"
                     class="size-9 object-contain dark:brightness-0 dark:invert">
            </span>
        </button>

        <ul id="mobile-radial-items"
            class="mobile-radial-items m-0 list-none p-0"
            x-bind:inert="!open"
            x-bind:aria-hidden="(!open).toString()"
            inert
            aria-hidden="true">
            @foreach ($items as $index => $item)
                <li class="mobile-radial-item"
                    style="--tx: {{ $radial[$index][0] }}; --ty: {{ $radial[$index][1] }}; --radial-index: {{ $index }}"
                    x-on:click="open = false; document.documentElement.classList.remove('overflow-hidden')">
                    @include('components.storefront.mobile-nav-item', ['item' => $item, 'index' => $index])
                </li>
            @endforeach
        </ul>
    </div>
</nav>
