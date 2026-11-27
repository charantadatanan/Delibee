<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="theme-color" content="#FFF8E7" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#12100D" media="(prefers-color-scheme: dark)" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="{{ asset('images/brand/deliBEE-mark-small.png') }}" type="image/png" sizes="270x270">
<link rel="apple-touch-icon" href="{{ asset('images/brand/deliBEE-mark-small.png') }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
