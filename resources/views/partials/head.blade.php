<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="theme-color" content="#FFF8E7" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#12100D" media="(prefers-color-scheme: dark)" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="16x16 32x32 48x48">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32.png') }}?v=2">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/brand/favicon-192.png') }}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
