<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main role="main" class="pb-28 lg:pb-10">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
