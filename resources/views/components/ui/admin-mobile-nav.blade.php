@php
    $items = [
        ['route' => 'admin.businesses', 'label' => 'Shops', 'icon' => 'building-storefront', 'match' => 'admin.businesses'],
        ['route' => 'admin.orders', 'label' => 'Orders', 'icon' => 'cube', 'match' => 'admin.orders'],
        ['route' => 'admin.reviews', 'label' => 'Reviews', 'icon' => 'star', 'match' => 'admin.reviews'],
        ['route' => 'marketplace', 'label' => 'Market', 'icon' => 'shopping-bag', 'match' => 'marketplace'],
        ['route' => 'dashboard', 'label' => 'Profile', 'icon' => 'user', 'match' => 'dashboard'],
    ];
@endphp

<nav aria-label="Hive Guard navigation"
     class="mobile-nav-bar fixed inset-x-0 bottom-0 z-50 border-t border-ink/15 bg-white/98 pb-[max(0.35rem,env(safe-area-inset-bottom))] backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-950/98">
    <div class="mx-auto grid max-w-md grid-cols-5 items-end px-1 pt-1.5">
        @foreach ($items as $item)
            @php($active = request()->routeIs($item['match']))

            <a href="{{ route($item['route']) }}"
               @if ($active) aria-current="page" @endif
               class="flex min-h-12 flex-col items-center justify-end gap-0.5 rounded-xl px-1 pb-1 text-[10px] font-medium transition {{ $active ? 'text-ink dark:text-honey' : 'text-zinc-500 dark:text-zinc-400' }}"
               wire:navigate>
                <span class="grid size-8 place-items-center rounded-full transition {{ $active ? 'bg-honey/25' : '' }}">
                    <flux:icon name="{{ $item['icon'] }}" class="size-[19px]" />
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
