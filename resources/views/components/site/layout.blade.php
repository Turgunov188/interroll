@props([
    'title',
    'description' => null,
    'brand' => true,      // append " — Inter Roll" to the title
    'cta' => null,        // where the header button leads; the contact form by default
    'image' => null,      // og:image
])
@php
    $fullTitle = $brand ? $title.' — '.$site->t('meta.suffix') : $title;
    $alternates = $site->alternates();
@endphp
<!doctype html>
<html lang="{{ $site->locale->value }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<title>{{ $fullTitle }}</title>
@if ($description)
<meta name="description" content="{{ $description }}">
@endif
<link rel="canonical" href="{{ $site->canonical() }}">
@foreach ($alternates as $lang => $url)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $alternates[\App\Enums\Locale::main()->value] }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Inter Roll">
<meta property="og:title" content="{{ $title }}">
@if ($description)
<meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:url" content="{{ $site->canonical() }}">
@if ($image)
<meta property="og:image" content="{{ $image }}">
@endif
<meta name="theme-color" content="#111418">
{{-- scroll animations hide content until shown; if the site script never starts, show everything --}}
<script>document.documentElement.classList.add('js');setTimeout(function(){if(!window.Alpine)document.documentElement.classList.remove('js')},3000)</script>
@vite(['resources/css/site.css', 'resources/js/site.js'])
{{ $head ?? '' }}
</head>
<body>
<a class="skip-link" href="#main">{{ $site->t('nav.skip') }}</a>
<canvas class="page-mesh" data-mesh aria-hidden="true"></canvas>

<div class="site" x-data>
    <x-site.header :cta="$cta ?? $site->url('contact').'#request'" />

    <main id="main">
        {{ $slot }}
    </main>

    <x-site.footer />
</div>

{{ $after ?? '' }}

<script type="application/json" id="site-i18n">@json($site->t('js'))</script>
@if (session('lead_number'))
<script type="application/json" id="lead-flash">@json(['number' => session('lead_number')])</script>
@endif
</body>
</html>
