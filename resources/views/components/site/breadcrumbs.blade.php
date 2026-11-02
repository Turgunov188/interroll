{{-- <x-site.breadcrumbs :items="[$site->t('common.home') => $site->url('index'), 'Каталог' => null]" /> --}}
@props(['items'])
<nav {{ $attributes }} aria-label="{{ $site->t('nav.breadcrumbs') }}">
    <ol class="breadcrumbs">
        @foreach ($items as $label => $url)
            <li>@if ($url)<a href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
        @endforeach
    </ol>
</nav>
