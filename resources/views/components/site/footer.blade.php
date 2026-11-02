<footer class="site-footer">
    <div class="container site-footer__inner">
        <span class="site-footer__brand">{{ $site->t('common.brand') }}</span>
        <nav class="site-footer__links" aria-label="{{ $site->t('nav.menu') }}">
            @foreach ($site->menu('footer') as $item)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <span>{{ $site->t('common.copyright', ['year' => now()->year]) }}</span>
    </div>
</footer>
