@props([
    'title' => null,
    'back' => null,
    'logo' => true,
])

<header {{ $attributes->merge(['class' => 'sticky top-0 z-40 -mx-4 border-b border-ink/10 bg-paper/95 px-4 py-2.5 backdrop-blur-md lg:hidden dark:border-zinc-800 dark:bg-zinc-950/95']) }}>
    <div class="flex items-center gap-3">
        <a href="{{ $back ?? route('marketplace') }}"
           class="ui-back size-9"
           aria-label="Go back"
           wire:navigate>
            <flux:icon name="chevron-left" class="size-5" />
        </a>

        <div class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5">
            @if ($logo)
                <x-ui.bee-logo class="h-auto w-20" />
            @endif

            @if ($title)
                <h1 class="max-w-full truncate font-display text-base font-semibold leading-tight">{{ $title }}</h1>
            @endif
        </div>

        <div class="flex min-w-9 items-center justify-end gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>
</header>
