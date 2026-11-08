@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'deliBEE')" {{ $attributes }}>
        <x-slot name="logo" class="clip-hex flex aspect-square size-8 items-center justify-center bg-honey font-display text-lg font-bold text-ink">d</x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'deliBEE')" {{ $attributes }}>
        <x-slot name="logo" class="clip-hex flex aspect-square size-8 items-center justify-center bg-honey font-display text-lg font-bold text-ink">d</x-slot>
    </flux:brand>
@endif
