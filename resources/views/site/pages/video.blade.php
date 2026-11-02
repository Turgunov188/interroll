{{-- Videos. Data: App\Services\Site\PageData\VideosPageData ($videos, $shorts). --}}
<x-site.layout :title="$site->t('meta.video.title')" :description="$site->t('meta.video.description')">
    <section class="section section--flush-bottom">
        <div class="container cluster cluster--between cluster--end" style="--cluster-gap: 20px">
            <div class="stack">
                <p class="eyebrow">{{ $site->t('video.eyebrow') }}</p>
                <h1 class="h1">{{ $site->t('video.title') }} <span class="hl">{{ $site->t('video.accent') }}</span></h1>
            </div>
            <p class="lead video-lead">{{ $site->t('video.lead') }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if (! $videos)
                <p class="lead">{{ $site->t('video.empty') }}</p>
            @else
                <div class="video-layout" x-data="videoPlayer(@js($videos))">
                    <div class="video-player">
                        <div class="video-player__screen">
                            <button type="button" class="video-player__poster" x-show="!playing" :style="current.poster ? { backgroundImage: `url('${current.poster}')` } : {}" @click="play()" aria-label="{{ $site->t('video.play') }}">
                                <span class="play-button" aria-hidden="true"><svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4l14 8-14 8z"/></svg></span>
                            </button>
                            <template x-if="playing && current.youtubeId">
                                <iframe :src="youtubeSrc" :title="current.title" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                            </template>
                            <template x-if="playing && !current.youtubeId && current.file">
                                <video x-ref="video" :src="current.file" :poster="current.poster" controls autoplay playsinline @timeupdate="progress = $event.target.currentTime / ($event.target.duration || current.seconds)"></video>
                            </template>
                        </div>
                        <div class="video-player__bar">
                            <div class="video-player__track" x-show="!current.youtubeId">
                                <span class="video-player__progress" :style="{ width: (progress * 100) + '%' }"></span>
                                <template x-for="chapter in current.chapters" :key="chapter.at"><span class="video-player__mark" :style="{ left: (chapter.position * 100) + '%' }"></span></template>
                            </div>
                            <div class="cluster cluster--between meta video-player__time"><span x-text="current.duration"></span><span>{{ $site->t('video.subtitles') }}</span></div>
                        </div>
                        <div class="video-player__info stack stack--s">
                            <span class="label" x-text="current.category"></span>
                            <b class="h3" x-text="current.title"></b>
                            <span class="body-text" x-text="current.description"></span>
                            <div class="cluster" style="--cluster-gap: 8px">
                                <template x-for="chapter in current.chapters" :key="chapter.at">
                                    <button type="button" class="chip video-player__chapter" @click="seek(chapter.seconds)" x-text="chapter.at + ' · ' + chapter.label"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="panel video-playlist">
                        <div class="panel__head"><span>{{ $site->t('video.playlist') }}</span><span>{{ $site->t('video.count', ['count' => count($videos)]) }}</span></div>
                        @foreach ($videos as $i => $video)
                            <button type="button" class="video-playlist__item" :aria-current="index === {{ $i }}" @click="select({{ $i }})">
                                <x-site.media tone="dark" class="video-playlist__thumb" :src="$video->poster">
                                    <span class="video-playlist__length mono">{{ $video->duration }}</span>
                                </x-site.media>
                                <span class="stack stack--xs"><span class="label">{{ $video->category }}</span><b>{{ $video->title }}</b></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($shorts)
        <section class="section section--tint section--line-top">
            <div class="container">
                <div class="section-head">
                    <h2 class="h2">{{ $site->t('video.shorts.title') }}</h2>
                    <span class="eyebrow">{{ $site->t('video.shorts.platforms') }}</span>
                </div>
                <div class="grid" style="--grid-min: 200px; --grid-gap: 16px">
                    @foreach ($shorts as $short)
                        <a @if ($short->url) href="{{ $short->url }}" target="_blank" rel="noopener" @endif class="card short-video">
                            <x-site.media tone="dark" class="media--center short-video__media" :src="$short->poster">
                                <span class="play-button play-button--small" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4l14 8-14 8z"/></svg></span>
                                <span class="media__badge media__badge--bl mono">{{ $short->duration }}</span>
                            </x-site.media>
                            <span class="short-video__title">{{ $short->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-site.layout>
