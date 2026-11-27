@php
    $user = auth()->user();
    $cartCount = (int) $user->cartItems()->sum('quantity');
    $orderCount = $user->orders()->count();
    $reviewCount = $user->reviews()->count();
    $points = $user->pointsBalance();
    $tier = \App\Support\BeeTier::for($points);
    $profile = $user->profile;
    $recentOrders = $user->orders()->with('business')->latest('created_at')->take(3)->get();

    $menu = [
        ['label' => 'My Orders', 'hint' => $orderCount.' '.Illuminate\Support\Str::plural('order', $orderCount), 'icon' => 'cube', 'route' => route('orders.index')],
        ['label' => 'My Reviews', 'hint' => $reviewCount.' '.Illuminate\Support\Str::plural('review', $reviewCount), 'icon' => 'star', 'route' => route('reviews.index')],
        ['label' => 'Save Items', 'hint' => 'Coming soon', 'icon' => 'bookmark', 'route' => null],
        ['label' => 'Addresses', 'hint' => 'Set at checkout', 'icon' => 'map-pin', 'route' => null],
        ['label' => 'Payment Method', 'hint' => 'Payments on hold', 'icon' => 'wallet', 'route' => null],
    ];
@endphp

<x-layouts::app :title="__('Profile')">
    <div class="space-y-5">
        <x-ui.mobile-header :title="__('Profile')" :back="route('marketplace')">
            <x-slot:actions>
                <a href="{{ route('settings.index') }}" class="grid size-9 place-items-center rounded-full border border-ink/50 bg-white dark:border-zinc-700 dark:bg-zinc-900" aria-label="Settings" wire:navigate>
                    <flux:icon name="cog-6-tooth" class="size-4" />
                </a>
            </x-slot:actions>
        </x-ui.mobile-header>

        <div class="grid gap-5 lg:grid-cols-[380px_1fr] lg:items-start">
            {{-- Profile identity --}}
            <aside class="space-y-4">
                <section class="relative overflow-hidden rounded-[28px] border border-ink/70 bg-hero dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="absolute inset-0 bg-comb opacity-45 dark:opacity-10"></div>

                    <div class="relative p-5">
                        <div class="flex items-center gap-4">
                            <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-full border-2 border-ink bg-white font-display text-2xl font-bold text-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey">
                                @if ($profile?->avatar_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}" alt="" class="size-full object-cover">
                                @else
                                    {{ $user->initials() }}
                                @endif
                            </span>

                            <div class="min-w-0">
                                <h1 class="truncate font-display text-2xl font-semibold leading-tight">{{ $user->name }}</h1>
                                @if ($user->username)
                                    <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">{{ '@'.$user->username }}</p>
                                @endif
                                <span class="mt-2 inline-flex items-center gap-1 rounded-full border border-ink/40 px-2.5 py-0.5 text-[10px] font-semibold {{ $tier['badge'] }} text-ink">
                                    <span aria-hidden="true">♛</span>
                                    {{ $tier['name'] }}
                                </span>
                            </div>
                        </div>

                        @if ($profile?->bio)
                            <p class="mt-4 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $profile->bio }}</p>
                        @endif

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl border border-ink/25 bg-white/70 p-2.5 dark:bg-zinc-950">
                                <p class="font-display text-xl font-semibold">{{ number_format($orderCount) }}</p>
                                <p class="text-[10px] text-zinc-600 dark:text-zinc-400">Orders</p>
                            </div>
                            <div class="rounded-xl border border-ink/25 bg-white/70 p-2.5 dark:bg-zinc-950">
                                <p class="font-display text-xl font-semibold">{{ number_format($reviewCount) }}</p>
                                <p class="text-[10px] text-zinc-600 dark:text-zinc-400">Reviews</p>
                            </div>
                            <div class="rounded-xl border border-ink/25 bg-white/70 p-2.5 dark:bg-zinc-950">
                                <p class="font-display text-xl font-semibold">{{ number_format($points) }}</p>
                                <p class="text-[10px] text-zinc-600 dark:text-zinc-400">Honey Points</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between text-[10px] font-medium text-zinc-600 dark:text-zinc-400">
                                <span>{{ $tier['name'] }} tier</span>
                                @if ($tier['next'])
                                    <span>{{ number_format($tier['next_at'] - $points) }} pts to {{ $tier['next'] }}</span>
                                @else
                                    <span>Top of the hive</span>
                                @endif
                            </div>
                            <div class="mt-1.5 h-2.5 overflow-hidden rounded-full border border-ink/30 bg-white/80">
                                <div class="h-full rounded-full bg-honey-deep" style="width: {{ $tier['progress'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card bg-paper p-3 dark:bg-zinc-900">
                    <a href="{{ route('rewards') }}" class="ui-row" wire:navigate>
                        <span class="flex items-center gap-2">
                            <flux:icon name="gift" class="size-4" />
                            Honey Rewards
                        </span>
                        <flux:icon name="chevron-right" class="size-4" />
                    </a>

                    <a href="{{ route('chat.index') }}" class="ui-row mt-2" wire:navigate>
                        <span class="flex items-center gap-2">
                            <flux:icon name="chat-bubble-left-right" class="size-4" />
                            Bee Chat
                        </span>
                        <flux:icon name="chevron-right" class="size-4" />
                    </a>

                    @unless ($user->hasRole('seller'))
                        <a href="{{ route('seller.onboarding') }}" class="ui-row mt-2" wire:navigate>
                            <span class="flex items-center gap-2">
                                <flux:icon name="building-storefront" class="size-4" />
                                Sell on deliBEE
                            </span>
                            <flux:icon name="chevron-right" class="size-4" />
                        </a>
                    @endunless
                </section>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-full border border-[#E7B9BC] bg-[#F8DDE0] px-4 py-2.5 text-sm font-semibold text-[#8A2E39] transition hover:bg-[#F4CDD1]">
                        Log out
                    </button>
                </form>
            </aside>

            <div class="space-y-4">
                {{-- Account menu --}}
                <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-xl font-semibold">Your hive</h2>
                        <a href="{{ route('settings.index') }}" class="text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>Settings</a>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach ($menu as $item)
                            @if ($item['route'])
                                <a href="{{ $item['route'] }}" class="ui-row" wire:navigate>
                                    <span class="flex min-w-0 items-center gap-2.5">
                                        <flux:icon name="{{ $item['icon'] }}" class="size-4 shrink-0" />
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </span>
                                    <span class="flex items-center gap-2 text-[10px] text-zinc-700">
                                        {{ $item['hint'] }}
                                        <flux:icon name="chevron-right" class="size-4" />
                                    </span>
                                </a>
                            @else
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-ink/20 bg-white/60 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400" aria-disabled="true">
                                    <span class="flex items-center gap-2.5">
                                        <flux:icon name="{{ $item['icon'] }}" class="size-4" />
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="rounded-full border border-ink/15 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide">{{ $item['hint'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>

                {{-- Quick stats --}}
                <section class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <a href="{{ route('cart.index') }}" class="ui-card bg-white/80 p-4 transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900" wire:navigate>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Honey Pot</p>
                        <p class="mt-1 font-display text-3xl font-semibold">{{ number_format($cartCount) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ \Illuminate\Support\Str::plural('item', $cartCount) }} waiting</p>
                    </a>

                    <a href="{{ route('orders.index') }}" class="ui-card bg-white/80 p-4 transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900" wire:navigate>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Bee Flight</p>
                        <p class="mt-1 font-display text-3xl font-semibold">{{ number_format($orderCount) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ \Illuminate\Support\Str::plural('order', $orderCount) }} placed</p>
                    </a>

                    <a href="{{ route('rewards') }}" class="ui-card bg-white/80 p-4 transition hover:-translate-y-0.5 hover:border-ink dark:bg-zinc-900 max-sm:col-span-2" wire:navigate>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Honey Points</p>
                        <p class="mt-1 font-display text-3xl font-semibold">{{ number_format($points) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $tier['name'] }} tier</p>
                    </a>
                </section>

                {{-- Recent orders --}}
                <section class="ui-card bg-paper p-4 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-xl font-semibold">Recent orders</h2>
                        <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>All orders</a>
                    </div>

                    @if ($recentOrders->isEmpty())
                        <p class="mt-3 rounded-xl border border-dashed border-ink/25 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            No orders yet. Your first one starts in the marketplace.
                        </p>
                    @else
                        <ul class="mt-3 divide-y divide-ink/10 dark:divide-zinc-800">
                            @foreach ($recentOrders as $order)
                                <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('orders.show', $order->number) }}"
                                           class="truncate font-medium transition hover:text-honey-deep dark:hover:text-honey"
                                           wire:navigate>
                                            {{ $order->business?->name ?? 'Seller' }}
                                        </a>
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                            {{ $order->number }} · {{ $order->created_at?->format('j M Y') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <x-ui.order-status :status="$order->status" />
                                        <span class="ui-price text-sm">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-layouts::app>
