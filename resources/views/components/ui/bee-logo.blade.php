@props([
    'variant' => 'full',
    'alt' => 'deliBEE',
])

@if ($variant === 'mark')
    <img src="{{ asset('images/brand/deliBEE-mark.png') }}"
         alt="{{ $alt }}"
         {{ $attributes->merge(['class' => 'block object-contain']) }}>
@else
    <svg {{ $attributes->merge(['class' => 'block overflow-visible']) }}
         viewBox="0 0 379.764 235.089"
         fill="none"
         xmlns="http://www.w3.org/2000/svg"
         role="img"
         aria-label="{{ $alt }}">
        <image href="{{ asset('images/brand/deliBEE-mark.png') }}"
               x="37.745"
               y="10.745"
               width="213.6"
               height="213.6"
               transform="rotate(6.1 144.545 117.545)"
               preserveAspectRatio="xMidYMid meet" />

        <text x="0"
              y="178"
              class="font-display fill-ink dark:fill-zinc-100"
              font-size="64"
              font-weight="700"
              letter-spacing="-1">deli</text>

        <text x="197"
              y="172"
              class="font-display fill-[#FBB007]"
              font-size="96"
              font-weight="600"
              letter-spacing="-1">EE</text>
    </svg>
@endif
