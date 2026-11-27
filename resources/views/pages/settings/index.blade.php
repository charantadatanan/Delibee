@php
    $user = auth()->user();
    $profile = $user->profile;

    $account = [
        ['label' => 'Edit Profile', 'hint' => null, 'icon' => 'user', 'href' => route('profile.edit')],
        ['label' => 'Change Password', 'hint' => null, 'icon' => 'lock-closed', 'href' => route('security.edit')],
        ['label' => 'Email Address', 'hint' => $user->email, 'icon' => 'envelope', 'href' => route('profile.edit')],
        ['label' => 'Phone No.', 'hint' => $profile?->phone ?? 'Add phone', 'icon' => 'phone', 'href' => route('profile.edit')],
    ];

    $app = [
        ['label' => 'Notifications', 'hint' => 'Soon', 'icon' => 'bell', 'href' => null],
        ['label' => 'Language', 'hint' => 'English', 'icon' => 'globe-alt', 'href' => null],
        ['label' => 'Appearance', 'hint' => 'Light / Dark', 'icon' => 'moon', 'href' => route('appearance.edit')],
    ];

    $support = [
        ['label' => 'Help Center', 'hint' => null, 'icon' => 'question-mark-circle', 'href' => route('help')],
        ['label' => 'Terms & Condition', 'hint' => null, 'icon' => 'document-text', 'href' => route('legal.terms')],
        ['label' => 'Privacy Policy', 'hint' => null, 'icon' => 'shield-check', 'href' => route('legal.privacy')],
    ];
@endphp

<x-layouts::app :title="__('Settings')">
    <div class="mx-auto max-w-3xl space-y-5">
        <x-ui.mobile-header :title="__('Settings')" :logo="false" :back="route('dashboard')" />

        <div class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Your account</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Settings</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage your profile, security and app preferences.</p>
        </div>

        @foreach ([['Account', $account], ['App Reference', $app], ['Support', $support]] as [$heading, $items])
            <section>
                <h2 class="mb-2 px-1 font-display text-lg font-semibold">{{ $heading }}</h2>

                <div class="space-y-2">
                    @foreach ($items as $item)
                        @if ($item['href'])
                            <a href="{{ $item['href'] }}" class="ui-row min-h-12" wire:navigate>
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <flux:icon name="{{ $item['icon'] }}" class="size-4 shrink-0" />
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </span>
                                <span class="flex min-w-0 items-center gap-2 text-[11px] text-zinc-700">
                                    @if ($item['hint'])
                                        <span class="max-w-40 truncate">{{ $item['hint'] }}</span>
                                    @endif
                                    <flux:icon name="chevron-right" class="size-4 shrink-0" />
                                </span>
                            </a>
                        @else
                            <div class="flex min-h-12 items-center justify-between gap-3 rounded-xl border border-ink/20 bg-white/60 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400" aria-disabled="true">
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
        @endforeach

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-[#E7B9BC] bg-[#F8DDE0] px-4 py-2.5 text-sm font-semibold text-[#8A2E39] transition hover:bg-[#F4CDD1]">
                <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                Log Out
            </button>
        </form>
    </div>
</x-layouts::app>
