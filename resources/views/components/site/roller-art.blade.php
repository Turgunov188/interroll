{{--
    Drawn roller for a before / after slot that has no photo yet: a worn roller or the same roller
    recoated. It is an illustration, so the slot says so (media.blade.php adds the label).
    <x-site.roller-art state="worn" tone="conveyor" />   tones: conveyor (yellow PU) · printing (rubber) · press (red PU)
--}}
@props(['state' => 'restored', 'tone' => 'conveyor'])
@php
    $id = 'ra'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
    $worn = $state === 'worn';
    // body gradient top → middle → bottom, and the lighter end face
    $palette = [
        'conveyor' => ['restored' => ['#ffe17a', '#f5b700', '#a87800', '#ffd84d'], 'worn' => ['#a8956a', '#7d6c45', '#4d4229', '#958459']],
        'printing' => ['restored' => ['#6b7581', '#2f3640', '#14181d', '#4a525c'], 'worn' => ['#7d7a74', '#57534d', '#34312d', '#6c6963']],
        'press' => ['restored' => ['#f7926b', '#d45a30', '#8a2f14', '#ea7a52'], 'worn' => ['#9d7466', '#73503f', '#462e24', '#8a6556']],
    ][$tone] ?? null;
    [$top, $mid, $bottom, $face] = ($palette ?? []) ? $palette[$worn ? 'worn' : 'restored'] : ['#ffe17a', '#f5b700', '#a87800', '#ffd84d'];

    // a straight body when restored, a chewed-up outline when worn
    $body = $worn
        ? 'M150,131 L190,128 L232,136 L270,129 L318,141 L352,130 L401,133 L438,127 L482,139 L525,131 L566,135 L606,128 L650,132 A26,103 0 0 1 650,334 L612,329 L570,336 L530,327 L488,333 L446,326 L400,332 L356,325 L310,334 L268,327 L226,333 L188,326 L150,331 A26,100 0 0 1 150,131 Z'
        : 'M150,126 H650 A26,104 0 0 1 650,334 H150 A26,104 0 0 1 150,126 Z';
@endphp
<svg {{ $attributes->class('roller-art') }} viewBox="0 0 800 460" preserveAspectRatio="xMidYMid meet" role="img" aria-hidden="true">
    <defs>
        <linearGradient id="{{ $id }}-body" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="{{ $top }}" /><stop offset="0.45" stop-color="{{ $mid }}" /><stop offset="1" stop-color="{{ $bottom }}" />
        </linearGradient>
        <linearGradient id="{{ $id }}-face" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="{{ $face }}" /><stop offset="1" stop-color="{{ $bottom }}" />
        </linearGradient>
        <linearGradient id="{{ $id }}-steel" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#dfe4e9" /><stop offset="0.5" stop-color="#9aa3ad" /><stop offset="1" stop-color="#59616a" />
        </linearGradient>
        <linearGradient id="{{ $id }}-shine" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity="0" /><stop offset="0.5" stop-color="#fff" stop-opacity="{{ $tone === 'printing' ? '0.28' : '0.55' }}" /><stop offset="1" stop-color="#fff" stop-opacity="0" />
        </linearGradient>
        <filter id="{{ $id }}-blur" x="-20%" y="-200%" width="140%" height="500%"><feGaussianBlur stdDeviation="9" /></filter>
        <clipPath id="{{ $id }}-clip"><path d="{{ $body }}" /></clipPath>
    </defs>

    <ellipse cx="410" cy="385" rx="310" ry="16" fill="#000" opacity="0.18" filter="url(#{{ $id }}-blur)" />

    {{-- left journal --}}
    <rect x="62" y="207" width="100" height="46" rx="4" fill="url(#{{ $id }}-steel)" />
    <ellipse cx="64" cy="230" rx="8" ry="23" fill="#7f8891" />

    {{-- coating --}}
    <path d="{{ $body }}" fill="url(#{{ $id }}-body)" />

    <g clip-path="url(#{{ $id }}-clip)">
        @if ($worn)
            {{-- worn through to the metal core --}}
            <path d="M262,128 C280,150 318,162 344,140 L352,124 Z M470,126 C488,156 540,170 572,146 L580,124 Z M300,336 C318,312 366,306 392,330 L396,340 Z" fill="url(#{{ $id }}-steel)" />
            {{-- circumferential grooves --}}
            @foreach ([205, 238, 296, 412, 447, 509, 598] as $x)
                <path d="M{{ $x }},124 Q{{ $x + 7 }},230 {{ $x }},338" fill="none" stroke="#000" stroke-opacity="0.28" stroke-width="3" />
                <path d="M{{ $x + 4 }},124 Q{{ $x + 11 }},230 {{ $x + 4 }},338" fill="none" stroke="#fff" stroke-opacity="0.12" stroke-width="2" />
            @endforeach
            {{-- cracks --}}
            <path d="M340,200 l14,9 -6,12 15,8 -4,14 12,9" fill="none" stroke="#1a1712" stroke-opacity="0.7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M548,232 l-10,11 9,9 -12,13 6,10" fill="none" stroke="#1a1712" stroke-opacity="0.7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            {{-- dirt --}}
            @foreach ([[180, 270, 5], [225, 182, 4], [372, 288, 6], [430, 176, 3], [498, 300, 5], [586, 196, 4], [622, 262, 6], [276, 236, 3]] as [$cx, $cy, $r])
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="#000" opacity="0.25" />
            @endforeach
        @else
            {{-- smooth, freshly ground surface --}}
            <rect x="120" y="146" width="560" height="34" fill="url(#{{ $id }}-shine)" />
            <rect x="120" y="292" width="560" height="14" fill="url(#{{ $id }}-shine)" opacity="0.35" />
        @endif
    </g>

    {{-- end face: coating ring, metal core, journal --}}
    <ellipse cx="650" cy="230" rx="26" ry="{{ $worn ? 101 : 104 }}" fill="url(#{{ $id }}-face)" />
    <ellipse cx="650" cy="230" rx="{{ $worn ? 20 : 16 }}" ry="{{ $worn ? 82 : 66 }}" fill="url(#{{ $id }}-steel)" />
    <rect x="648" y="207" width="96" height="46" rx="4" fill="url(#{{ $id }}-steel)" />
    <ellipse cx="744" cy="230" rx="8" ry="23" fill="#c9d0d7" />
    <ellipse cx="744" cy="230" rx="3" ry="8" fill="#59616a" />
</svg>
