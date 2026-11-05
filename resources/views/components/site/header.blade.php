@props(['cta'])
<header class="site-header" x-data="siteHeader" @keydown.escape.window="close()">
    <div class="container site-header__bar">
        <a href="{{ $site->url('index') }}" class="logo" aria-label="Inter Roll — {{ $site->t('nav.index') }}">
            <img class="logo__mark" src="{{ asset('images/brand/inter-roll.svg') }}" width="44" height="44" alt="">
            <span class="logo__name">{{ $site->t('common.brand') }}</span>
        </a>

        <div id="site-menu" class="site-header__menu" :class="{ 'is-open': menuOpen }">
            <nav class="nav" aria-label="{{ $site->t('nav.menu') }}">
                @foreach ($site->menu('main') as $item)
                    <a href="{{ $item['url'] }}" class="nav__link"@if ($item['current']) aria-current="page"@endif>{{ $item['label'] }}</a>
                @endforeach

                @php($more = $site->menu('more'))
                <div class="nav__more" @click.outside="moreOpen = false" @mouseenter="moreOpen = true" @mouseleave="moreOpen = false">
                    <button type="button" class="nav__more-toggle" :aria-expanded="moreOpen" aria-controls="nav-more" @click="moreOpen = !moreOpen">
                        {{ $site->t('nav.more') }}
                        <svg aria-hidden="true" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 1l4 4 4-4"/></svg>
                    </button>
                    <div id="nav-more" class="nav__menu" x-show="moreOpen" x-transition.opacity.duration.150ms x-cloak>
                        <div class="nav__menu-list">
                            @foreach ($more as $item)
                                <a href="{{ $item['url'] }}"@if ($item['current']) aria-current="page"@endif>{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </nav>

            <x-site.language-switch />
        </div>

        <div class="site-header__actions">
            <a href="{{ $cta }}" class="btn btn--accent btn--mono site-header__cta" @click="close()">{{ $site->t('common.send_drawing') }}</a>
            <button type="button" class="burger" aria-controls="site-menu" :aria-expanded="menuOpen" aria-label="{{ $site->t('nav.open_menu') }}" @click="toggleMenu()">
                <span class="burger__lines"></span>
            </button>
        </div>
    </div>
    <div class="site-header__progress" aria-hidden="true"></div>
</header>
