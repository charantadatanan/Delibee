@php
    $user = auth()->user();
    $points = $user?->pointsBalance() ?? 0;
    $tier = $points >= 1000 ? 'Queen bee' : ($points >= 300 ? 'Worker bee' : 'New bee');
@endphp

<header class="sticky top-0 z-40 border-b border-transparent bg-paper/95 backdrop-blur-md lg:hidden dark:border-zinc-800 dark:bg-zinc-950/90">
    <div class="flex items-center gap-3 px-4 py-2.5">
        <a href="{{ route('marketplace') }}"
           class="flex min-w-0 items-center"
           aria-label="deliBEE marketplace"
           wire:navigate>
            <img src="{{ asset('images/mobile/bee-logo.png') }}"
                 alt="deliBEE"
                 width="147"
                 height="70"
                 class="mobile-bee-logo h-14 w-[147px] object-cover dark:brightness-0 dark:invert">
        </a>

        <div class="ms-auto flex items-center gap-2">
            @auth
                <a href="{{ route('rewards') }}"
                   class="inline-flex min-h-9 items-center gap-1 rounded-full bg-[#30200e] px-3 py-1.5 text-[10px] font-semibold text-paper transition hover:bg-honey-deep dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey"
                   wire:navigate>
                    <span aria-hidden="true">♛</span>
                    <span class="max-w-24 truncate">{{ $tier }} Tier</span>
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="rounded-full bg-[#30200e] px-3 py-1.5 text-[10px] font-semibold text-paper transition hover:bg-honey-deep dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey"
                   wire:navigate>
                    Join deliBEE
                </a>
            @endauth

            <a href="{{ auth()->check() ? route('chat.index') : route('login') }}"
               class="grid size-10 place-items-center rounded-full border border-ink/30 bg-white transition hover:border-honey-deep/40 dark:border-zinc-700 dark:bg-zinc-900"
               aria-label="Bee Chat"
               wire:navigate>
                <img src="{{ asset('images/mobile/chat.png') }}"
                     alt=""
                     width="22"
                     height="22"
                     class="size-[22px] object-contain dark:brightness-0 dark:invert">
            </a>
        </div>
    </div>
</header>
