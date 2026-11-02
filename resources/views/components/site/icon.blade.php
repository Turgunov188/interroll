{{-- Line icon from an SVG path (24×24 by default; category icons are 64×64). --}}
@props(['path', 'size' => 24, 'box' => 24, 'stroke' => 2])
<svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $box }} {{ $box }}" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
