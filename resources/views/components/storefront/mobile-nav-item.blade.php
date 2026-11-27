@php
    $isActive = request()->routeIs($item['active']);
    $badge = $item['badge'] ?? 0;
@endphp

<a href="{{ route($item['route']) }}"
   class="mobile-nav-item group relative flex flex-col items-center {{ $isActive ? 'is-active' : '' }}"
   style="--nav-index: {{ $index }}; margin-top: {{ $item['y'] ?? 0 }}px"
   @if ($isActive) aria-current="page" @endif
   wire:navigate>
    <span class="nav-bubble relative grid size-11 place-items-center rounded-full border-2 border-paper shadow-[0_6px_14px_rgba(30,30,30,0.18)] transition group-active:scale-95 dark:border-zinc-950 {{ $isActive ? 'bg-ink text-paper dark:bg-zinc-100 dark:text-zinc-900' : 'bg-honey text-ink dark:bg-honey-deep dark:text-paper' }}">
        <flux:icon :name="$item['icon']" class="size-5" />

        @if ($badge > 0)
            <span class="absolute -end-1 -top-1 grid min-w-4 place-items-center rounded-full border border-ink/20 bg-paper px-1 text-[9px] font-semibold text-ink shadow-sm dark:border-zinc-400 dark:bg-zinc-100 dark:text-zinc-900">
                {{ $badge }}
            </span>
        @endif
    </span>

    <span class="mobile-nav-label text-[11px] font-semibold leading-[14px] text-ink/80 dark:text-paper/85">{{ $item['label'] }}</span>
</a>
