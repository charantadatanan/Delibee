<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <main class="grid min-h-svh lg:grid-cols-[1.02fr_1fr]">
            <aside class="relative hidden overflow-hidden bg-hero p-12 lg:flex lg:flex-col lg:justify-between dark:bg-zinc-900">
                <div class="absolute inset-0 bg-comb opacity-50 dark:opacity-10"></div>

                <div class="relative flex items-center gap-3">
                    <x-ui.bee-logo class="h-auto w-40" />
                </div>

                <div class="relative max-w-lg py-12">
                    <p class="inline-flex items-center gap-2 rounded-full border border-ink/40 bg-paper/80 px-3 py-1 text-xs font-semibold">
                        <span class="size-1.5 rounded-full bg-leaf" aria-hidden="true"></span>
                        Shop local. Grow together.
                    </p>

                    <h2 class="mt-6 font-display text-5xl font-semibold leading-[1.02] tracking-tight">
                        The hive rewards the sellers doing good work right now.
                    </h2>

                    <p class="mt-5 max-w-md text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                        One basket across many local businesses, verified ratings from delivered orders, and a
                        monthly ranking that a new shop can climb.
                    </p>

                    <ul class="mt-8 grid gap-3 text-sm">
                        <li class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-full border border-ink/50 bg-honey">
                                <flux:icon name="check" class="size-4" />
                            </span>
                            Verified reviews only
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-full border border-ink/50 bg-honey">
                                <flux:icon name="check" class="size-4" />
                            </span>
                            One order per seller, tracked separately
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-full border border-ink/50 bg-honey">
                                <flux:icon name="check" class="size-4" />
                            </span>
                            Honey Points on every purchase
                        </li>
                    </ul>
                </div>

                <div class="relative -mx-12 -mb-12 mt-6">
                    <x-ui.hills class="h-52 w-full" />
                </div>
            </aside>

            <section class="relative flex min-h-svh flex-col px-5 pb-32 pt-8 sm:px-10 lg:justify-center lg:pb-16">
                <div class="absolute end-4 top-4 z-20">
                    <x-ui.theme-toggle />
                </div>

                <div class="flex justify-center lg:hidden">
                    <a href="{{ route('marketplace') }}" class="inline-flex flex-col items-center gap-1" wire:navigate>
                        <x-ui.bee-logo class="h-auto w-40" />
                        <span class="font-display text-sm text-honey-deep dark:text-honey">shop. support. grow together.</span>
                    </a>
                </div>

                <div class="relative z-10 mx-auto my-auto w-full max-w-md pt-8 lg:pt-0">
                    {{ $slot }}
                </div>

                <p class="relative z-10 mt-8 text-center text-xs">
                    <a href="{{ route('marketplace') }}"
                       class="font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey"
                       wire:navigate>
                        Back to the marketplace
                    </a>
                </p>

                <div class="pointer-events-none absolute inset-x-0 bottom-0 lg:hidden">
                    <x-ui.hills class="h-32 w-full opacity-90" />
                </div>
            </section>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
