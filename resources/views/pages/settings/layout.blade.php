@php
    $links = [
        ['label' => 'Profile', 'icon' => 'user', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
        ['label' => 'Security', 'icon' => 'lock-closed', 'href' => route('security.edit'), 'active' => request()->routeIs('security.edit')],
        ['label' => 'Appearance', 'icon' => 'moon', 'href' => route('appearance.edit'), 'active' => request()->routeIs('appearance.edit')],
    ];
@endphp

<div class="mx-auto max-w-4xl">
    <x-ui.mobile-header :title="$heading ?? __('Settings')" :logo="false" :back="route('settings.index')" />

    <div class="flex items-start gap-6">
        <aside class="hidden w-56 shrink-0 lg:block">
            <nav aria-label="{{ __('Settings') }}" class="ui-card sticky top-24 space-y-1 bg-paper p-2 dark:bg-zinc-900">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}"
                       @if ($link['active']) aria-current="page" @endif
                       class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-medium transition {{ $link['active'] ? 'border border-ink bg-honey text-ink' : 'text-zinc-600 hover:bg-white/70 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-950' }}"
                       wire:navigate>
                        <flux:icon name="{{ $link['icon'] }}" class="size-4" />
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('settings.index') }}" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-medium text-zinc-600 transition hover:bg-white/70 hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-950" wire:navigate>
                    <flux:icon name="arrow-left" class="size-4" />
                    All settings
                </a>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="hidden lg:block">
                <h2 class="font-display text-2xl font-semibold">{{ $heading ?? '' }}</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $subheading ?? '' }}</p>
            </div>

            <div class="ui-card mt-0 bg-paper p-4 dark:bg-zinc-900 lg:mt-5 lg:p-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
