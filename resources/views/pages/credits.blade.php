<x-storefront.layout :title="__('Photo Credits')">
    <article class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center gap-3 lg:hidden">
            <a href="{{ auth()->check() ? route('settings.index') : route('marketplace') }}" class="ui-back size-9" aria-label="Go back" wire:navigate>
                <flux:icon name="chevron-left" class="size-5" />
            </a>
            <h1 class="font-display text-xl font-semibold">Photo Credits</h1>
        </div>

        <header class="hidden lg:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-honey-deep dark:text-honey">Legal</p>
            <h1 class="mt-1 font-display text-4xl font-semibold tracking-tight">Photo Credits</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                Demo product photography is used as a stand-in until sellers supply their own photographs.
            </p>
        </header>

        <div class="ui-card bg-paper p-5 dark:bg-zinc-900 sm:p-6">
            <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                The current demo listings use the following photographs from Wikimedia Commons. Each image is
                linked to its source file and is used under the stated licence.
            </p>

            <ul class="mt-5 divide-y divide-ink/10 dark:divide-zinc-800">
                <li class="py-3">
                    <p class="font-medium">Fresh apples</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Shixart1985 · CC BY 2.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Fresh_red_apple_closeup.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
                <li class="py-3">
                    <p class="font-medium">Golden mangoes</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Ivar Leidus · CC BY-SA 4.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Mangos_-_single_and_halved.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
                <li class="py-3">
                    <p class="font-medium">Free-range eggs</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Krzysztof Golik · CC BY-SA 4.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Egg_cartons_with_chicken_eggs_03.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
                <li class="py-3">
                    <p class="font-medium">Wild honey</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Cindy Shebley · CC BY 2.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Antique_Glass_Honey_Jar_With_Rusted_Metal_Cap_(31009798197).jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
                <li class="py-3">
                    <p class="font-medium">Sourdough loaf</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Agelaia · CC BY 4.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Sourdough_Bread_Loaf.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
                <li class="py-3">
                    <p class="font-medium">Cold brew coffee</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Photo by Klientos · CC BY-SA 4.0 ·
                        <a class="underline underline-offset-4 hover:text-honey-deep dark:hover:text-honey" href="https://commons.wikimedia.org/wiki/File:Iced_B%E1%BA%A1c_X%E1%BB%89u_served_in_tall_glass_on_the_table_in_tha_cafe,_Danang_city,_2023.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a>
                    </p>
                </li>
            </ul>
        </div>
    </article>
</x-storefront.layout>
