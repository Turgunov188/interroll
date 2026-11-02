<nav class="lang-switch" aria-label="{{ $site->t('nav.languages') }}">
    @foreach ($site->alternates() as $lang => $url)
        <a href="{{ $url }}" hreflang="{{ $lang }}" lang="{{ $lang }}"@if ($lang === $site->locale->value) aria-current="true"@endif>{{ strtoupper($lang) }}</a>
    @endforeach
</nav>
