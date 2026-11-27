@props([
    'title',
    'description' => null,
    'icon' => 'archive-box',
])

<div {{ $attributes->merge(['class' => 'ui-card-cream flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-full border border-ink/50 bg-honey">
        <flux:icon name="{{ $icon }}" class="size-7 text-ink" />
    </span>

    <p class="mt-4 font-display text-xl font-semibold">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
