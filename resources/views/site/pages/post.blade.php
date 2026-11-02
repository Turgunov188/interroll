{{--
    A blog article: $post (PostPage from the admin, may be null for a custom article) and
    $bodyView — the Blade view of an article with its own interactive body (config site.blog.custom).
--}}
@php
    $custom = $bodyView ? str_replace('site.posts.', '', $bodyView) : null;
    $fallback = $custom === 'shore-a-hardness' ? $site->t('shore') : [];
    $title = $post?->card->title ?: ($fallback['title'] ?? '');
    $excerpt = $post?->card->excerpt ?: ($fallback['excerpt'] ?? '');
    $category = $post?->card->categoryName ?: ($fallback['category'] ?? '');
    $minutes = $post?->card->minutes ?? ($fallback['minutes'] ?? 1);
    $author = $post?->author ?: ($fallback['author'] ?? '');
    $toc = $bodyView
        ? collect($fallback['sections'] ?? [])->map(fn ($t, $id) => ['id' => $id, 'title' => $t])->values()->all()
        : ($post?->toc ?? []);
@endphp
<x-site.layout :title="$title" :description="$excerpt" :image="$post?->card->cover">
<article class="section post" x-data="tableOfContents">
    <div class="container container--narrow stack stack--l">
        <header class="stack post-head">
            <span class="cluster mono text-small" style="--cluster-gap: 12px">
                @if ($category)<span class="chip chip--accent">{{ $category }}</span>@endif
                <span>{{ collect([$site->t('blog.minutes_short', ['minutes' => $minutes]), $author])->filter()->implode(' · ') }}</span>
                @if ($post?->date)<span class="text-muted">{{ $post->date }}</span>@endif
            </span>
            <h1 class="h1 post-head__title">{{ $title }}</h1>
            <p class="lead post-head__lead">{{ $excerpt }}</p>
        </header>

        @if ($post?->card->cover)
            <figure class="panel post-cover">
                <img src="{{ $post->card->cover }}" alt="{{ $title }}">
                @if ($post->card->coverCaption !== $title)<figcaption class="meta">{{ $post->card->coverCaption }}</figcaption>@endif
            </figure>
        @elseif ($fallback)
            <x-site.media class="post-cover post-cover--placeholder" :caption="$fallback['cover']" />
        @endif

        <div class="post-layout">
            @if ($toc)
                <nav class="panel post-toc" aria-label="{{ $site->t('blog.post.toc') }}">
                    <span class="label post-toc__title">{{ $site->t('blog.post.toc') }}</span>
                    @foreach ($toc as $item)
                        <a href="#{{ $item['id'] }}" class="post-toc__link" :class="{ 'is-current': current === @js($item['id']) }">{{ $item['title'] }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="prose" x-ref="body">
                @if ($bodyView)
                    @include($bodyView)
                @else
                    {!! $post->body !!}
                @endif

                <div class="post-cta">
                    <span class="stack stack--xs"><span class="eyebrow text-accent">{{ $site->t('blog.post.finder_label') }}</span><b class="post-cta__title">{{ $site->t('blog.post.finder_title') }}</b></span>
                    <a href="{{ $site->url('part-finder') }}" class="btn btn--accent">{{ $site->t('blog.post.finder_action') }}</a>
                </div>
            </div>
        </div>
    </div>
</article>
</x-site.layout>
