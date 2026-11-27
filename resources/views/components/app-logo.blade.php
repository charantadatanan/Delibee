@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => 'inline-flex items-center']) }} aria-label="{{ config('app.name', 'deliBEE') }} dashboard">
    <x-ui.bee-logo class="h-auto w-32" />
</a>
