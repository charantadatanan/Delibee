@props([
    'title',
    'description',
])

<div class="text-center lg:text-left">
    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-honey-deep dark:text-honey">deliBEE account</p>
    <h1 class="mt-2 font-display text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $title }}</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
</div>
