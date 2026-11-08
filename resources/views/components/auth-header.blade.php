@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" level="1" class="font-display">{{ $title }}</flux:heading>
    <flux:subheading class="mt-1">{{ $description }}</flux:subheading>
</div>
