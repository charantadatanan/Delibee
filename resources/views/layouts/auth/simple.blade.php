<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="relative min-h-screen bg-paper font-sans text-ink antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="pointer-events-none absolute inset-0 bg-comb opacity-40 dark:opacity-10"></div>

        <main class="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-5">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2.5" wire:navigate>
                    <span class="clip-hex grid size-12 place-items-center bg-honey font-display text-2xl font-bold text-ink">d</span>
                    <span class="font-display text-2xl font-semibold tracking-tight">{{ config('app.name', 'deliBEE') }}</span>
                </a>

                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                    A marketplace ranked on how sellers are doing right now.
                </p>

                <div class="rounded-2xl border border-comb/90 bg-white/70 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
                    {{ $slot }}
                </div>

                <p class="text-center text-xs">
                    <a href="{{ route('marketplace') }}"
                       class="font-medium text-honey-deep hover:underline dark:text-honey"
                       wire:navigate>
                        Back to the marketplace
                    </a>
                </p>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
