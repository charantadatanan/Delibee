@php
    $user = auth()->user();
    $cartCount = (int) $user->cartItems()->sum('quantity');
    $orderCount = $user->orders()->count();
    $points = $user->pointsBalance();
    $recentOrders = $user->orders()->with('business')->latest('created_at')->take(3)->get();
@endphp

<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-3xl border border-comb bg-[#f7edd5] p-6 sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

            <div class="relative">
                <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">
                    Welcome back, {{ \Illuminate\Support\Str::before($user->name, ' ') }}.
                </h1>
                <p class="mt-2 max-w-xl text-zinc-600 dark:text-zinc-400">
                    Your Honey Pot, orders and points, plus the sellers performing well right now.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('marketplace') }}"
                       class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink"
                       wire:navigate>
                        Browse the marketplace
                    </a>
                    <a href="{{ route('ranking') }}"
                       class="rounded-lg border border-ink/15 bg-white/60 px-4 py-2 text-sm font-medium transition hover:border-ink/30 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500"
                       wire:navigate>
                        See the BeeRank
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('cart.index') }}"
               class="group rounded-2xl border border-comb/90 bg-white/70 p-5 transition-colors hover:border-honey-deep/40 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:border-honey/40"
               wire:navigate>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Honey Pot</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ number_format($cartCount) }}</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ \Illuminate\Support\Str::plural('item', $cartCount) }} waiting</p>
            </a>

            <a href="{{ route('orders.index') }}"
               class="group rounded-2xl border border-comb/90 bg-white/70 p-5 transition-colors hover:border-honey-deep/40 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:border-honey/40"
               wire:navigate>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Bee Flight</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ number_format($orderCount) }}</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ \Illuminate\Support\Str::plural('order', $orderCount) }} placed</p>
            </a>

            <a href="{{ route('rewards') }}"
               class="group rounded-2xl border border-comb/90 bg-white/70 p-5 transition-colors hover:border-honey-deep/40 dark:border-zinc-800 dark:bg-zinc-900/70 dark:hover:border-honey/40"
               wire:navigate>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Honey Rewards</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ number_format($points) }}</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Honey Points</p>
            </a>
        </div>

        <section class="rounded-2xl border border-comb/90 bg-white/70 p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">Recent orders</h2>
                <a href="{{ route('orders.index') }}" class="text-sm font-medium text-honey-deep hover:underline dark:text-honey" wire:navigate>
                    All orders
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <p class="mt-4 rounded-xl border border-dashed border-comb p-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    No orders yet. Your first one starts in the marketplace.
                </p>
            @else
                <ul class="mt-4 divide-y divide-comb/70 dark:divide-zinc-800">
                    @foreach ($recentOrders as $order)
                        @php($statusClasses = match ($order->status) {
                            \App\Enums\OrderStatus::Delivered => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
                            \App\Enums\OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                            \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::OutForDelivery => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-300',
                            default => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
                        })

                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div>
                                <a href="{{ route('orders.show', $order->number) }}"
                                   class="font-medium transition-colors hover:text-honey-deep dark:hover:text-honey"
                                   wire:navigate>
                                    {{ $order->business?->name ?? 'Seller' }}
                                </a>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Order {{ $order->number }}, placed {{ $order->created_at?->format('j M Y') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ $order->status->label() }}
                                </span>
                                <span class="font-semibold">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts::app>
