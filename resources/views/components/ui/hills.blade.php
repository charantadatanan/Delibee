@props(['class' => 'h-auto'])

<svg {{ $attributes->merge(['class' => 'ui-hills '.$class]) }}
     viewBox="0 0 390 170"
     fill="none"
     xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true"
     preserveAspectRatio="none">
    <path d="M0 104c38-34 77-42 118-24 34 15 62 17 96-2 51-29 107-21 176 24v68H0v-66Z" fill="#C7D08E"/>
    <path d="M0 124c47-25 84-25 119-8 37 18 65 20 100 6 52-21 107-10 171 27v21H0v-46Z" fill="#A9BA6D"/>
    <path d="M0 143c55-19 102-16 145 1 27 11 52 13 82 4 66-20 118-11 163 12v10H0v-27Z" fill="#E4C85B"/>

    <path d="M-8 116c-2-17 2-32 11-43 4 11 10 18 18 23-5 10-12 17-20 21l-9-1Z" fill="#7D9150"/>
    <path d="M28 137c-1-18 3-32 12-43 5 12 11 20 19 24-5 9-13 16-22 20l-9-1Z" fill="#788B4C"/>
    <path d="M383 118c2-18-2-33-12-44-4 12-10 19-18 24 5 10 12 17 21 21l9-1Z" fill="#7D9150"/>
    <path d="M346 141c1-18-4-32-13-42-4 11-10 19-18 23 5 10 13 16 22 20l9-1Z" fill="#788B4C"/>

    <g>
        <circle cx="54" cy="143" r="6" fill="#FFF8E7"/>
        <circle cx="54" cy="143" r="2.4" fill="#FFC233"/>
        <circle cx="79" cy="151" r="4.5" fill="#F6D9C0"/>
        <circle cx="79" cy="151" r="1.8" fill="#E59B45"/>
        <circle cx="336" cy="143" r="7" fill="#F3A65C"/>
        <circle cx="336" cy="143" r="2.8" fill="#7A4B18"/>
        <circle cx="313" cy="153" r="4.6" fill="#FFF8E7"/>
        <circle cx="313" cy="153" r="1.8" fill="#FFC233"/>
        <circle cx="356" cy="158" r="5.2" fill="#FFF8E7"/>
        <circle cx="356" cy="158" r="2" fill="#FFC233"/>
    </g>

    <g stroke="#7D9150" stroke-width="1.6" stroke-linecap="round">
        <path d="M54 149v8" />
        <path d="M336 150v9" />
        <path d="M313 158v6" />
    </g>
</svg>
