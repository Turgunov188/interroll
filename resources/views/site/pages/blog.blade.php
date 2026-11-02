{{-- Blog list. Data: App\Services\Site\PageData\BlogPageData ($featured, $posts, $categories). --}}
@php($filters = array_merge([new \App\Services\Site\ViewModels\Option('all', $site->t('common.all'))], $categories))
<x-site.layout :title="$site->t('meta.blog.title')" :description="$site->t('meta.blog.description')">
<div x-data="filterList({ items: @js(array_map(fn ($p) => ['key' => $p->slug, 'group' => $p->category], $posts)) })">
    <section class="section section--flush-bottom">
        <div class="container stack stack--l">
            <div class="cluster cluster--between cluster--end" style="--cluster-gap: 20px">
                <div class="stack">
                    <p class="eyebrow">{{ $site->t('blog.eyebrow') }}</p>
                    <h1 class="h1">{{ $site->t('blog.title') }} <span class="hl">{{ $site->t('blog.accent') }}</span></h1>
                </div>
                <p class="lead blog-lead">{{ $site->t('blog.lead') }}</p>
            </div>

            @if ($featured)
                <a href="{{ $featured->url }}" class="card blog-featured">
                    <x-site.media class="blog-featured__media" :src="$featured->cover" :caption="$featured->coverCaption" :alt="$featured->title" :lazy="false" />
                    <span class="blog-featured__body stack stack--m">
                        <span class="cluster mono text-small" style="--cluster-gap: 10px">
                            @if ($featured->categoryName)<span class="chip chip--accent">{{ $featured->categoryName }}</span>@endif
                            <span class="text-muted">{{ $site->t('blog.minutes', ['minutes' => $featured->minutes]) }}</span>
                        </span>
                        <b class="blog-featured__title blog-title">{{ $featured->title }}</b>
                        <span class="lead">{{ $featured->excerpt }}</span>
                        <span class="card__more">{{ $site->t('blog.read') }}</span>
                    </span>
                </a>
            @else
                <p class="lead">{{ $site->t('blog.empty') }}</p>
            @endif

            @if (count($categories) > 0)
                <div class="segmented segmented--accent blog-filter" role="group" aria-label="{{ $site->t('blog.rubrics') }}">
                    @foreach ($filters as $filter)
                        <button type="button" class="segmented__item" :aria-pressed="group === @js($filter->value)" @click="group = @js($filter->value)">{{ $filter->label }}</button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container grid" style="--grid-min: 340px; --grid-gap: 24px">
            @foreach ($posts as $post)
                <a href="{{ $post->url }}" class="card card-in blog-card" x-show="isShown(@js($post->slug))" :style="{ '--delay': delay(@js($post->slug)) }">
                    <x-site.media class="blog-card__media" :src="$post->cover" :caption="$post->coverCaption" :alt="$post->title" />
                    <span class="card__body">
                        <span class="cluster cluster--between mono blog-card__meta"><span>{{ $post->categoryName }}</span><span class="text-muted">{{ $site->t('blog.minutes_short', ['minutes' => $post->minutes]) }}</span></span>
                        <b class="card__title blog-title">{{ $post->title }}</b>
                        <span class="card__text">{{ $post->excerpt }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section section--tight section--dark">
        <div class="container cluster cluster--between" style="--cluster-gap: 28px">
            <div class="stack stack--s blog-channel">
                <span class="eyebrow text-accent">{{ $site->t('blog.channel.label') }}</span>
                <b class="h2">{{ $site->t('blog.channel.title') }}</b>
                <span class="mono text-small text-soft">{{ $site->t('blog.channel.text') }}</span>
            </div>
            <a href="https://t.me/{{ $site->setting('telegram_channel') }}" target="_blank" rel="noopener" class="btn btn--accent btn--lg">{{ $site->t('blog.channel.action') }}</a>
        </div>
    </section>
</div>
</x-site.layout>
