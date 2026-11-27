@props([])

<button type="button"
        x-data
        x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'"
        x-bind:aria-pressed="$flux.dark.toString()"
        {{ $attributes->merge(['class' => 'grid size-10 place-items-center rounded-full border border-ink/30 bg-white/70 text-ink transition hover:border-ink hover:bg-honey focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink active:scale-95 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800 dark:focus-visible:outline-honey']) }}
        aria-label="{{ __('Toggle light and dark theme') }}"
        title="{{ __('Toggle light and dark theme') }}">
    <flux:icon name="moon" class="size-5 dark:hidden" />
    <flux:icon name="sun" class="hidden size-5 dark:block" />
</button>
