{{-- About the factory. Data: App\Services\Site\PageData\FactoryPageData ($clients, $testimonials, $certificates). --}}
@php
    $zones = $site->t('factory.tour.zones');
    $zoneAreas = ['1 / 1 / 3 / 3', '3 / 1 / 5 / 3', '1 / 3 / 3 / 5', '3 / 3 / 5 / 5', '1 / 5 / 3 / 7', '3 / 5 / 5 / 7'];
    $pillarIcons = [
        'M3 21h18 M5 21V7l7-4 7 4v14 M9 21v-6h6v6',
        'M12 7v5l3 3 M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z',
        'M9 3h6 M10 3v6L4 20h16L14 9V3',
        'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z M9 12l2 2 4-4',
        'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
        'M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z M14 3v6h6 M8 13h8 M8 17h5',
    ];
    // photos from the IMPOL GROUP catalogue until the admin uploads others («Настройки сайта» → images)
    $groupPhotos = [
        ['factory_group_1', 'images/factory/impol-plant.webp'],
        ['factory_group_2', 'images/factory/impol-building.webp'],
        ['factory_group_3', 'images/factory/impol-meeting-room.webp'],
        ['factory_group_4', 'images/factory/impol-partners.webp'],
    ];
@endphp
<x-site.layout :title="$site->t('meta.factory.title')" :description="$site->t('meta.factory.description')">

    <section class="section--hero">
        <div class="container stack stack--l">
            <x-site.breadcrumbs class="rise" :items="[$site->t('common.home') => $site->url('index'), $site->t('factory.breadcrumb') => null]" />
            <div class="split split--end">
                <div class="stack stack--m split__wide" style="--split-basis: 560px">
                    <x-site.title tag="h1" class="display rise" style="--delay: 80ms" :lines="$site->t('factory.hero.lines')" :accent="$site->t('factory.hero.accent')" />
                    <p class="lead rise" style="--delay: 180ms">{{ $site->t('factory.hero.lead') }}</p>
                </div>
                <div class="panel panel--pad mono text-small stack stack--s factory-status rise" style="--delay: 260ms">
                    <span class="cluster" style="--cluster-gap: 10px"><span class="pulse-dot" aria-hidden="true"></span><span class="eyebrow">{{ $site->t('factory.hero.working') }}</span></span>
                    <span class="text-soft">{{ $site->setting('address') }}<br>{{ $site->t('factory.hero.hours') }}<br>{{ $site->t('factory.hero.shifts') }}</span>
                </div>
            </div>

            <div class="panel factory-stats rise" style="--delay: 340ms">
                @foreach ($site->t('factory.stats') as $stat)
                    @php($value = $site->number($stat['key']))
                    <div class="stat factory-stats__item">
                        <span class="stat__value"><span x-count-up="{{ $value }}">{{ $value ?: '[__]' }}</span><span class="stat__unit">{{ $stat['unit'] }}</span></span>
                        <span class="label">{{ $stat['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <x-site.media class="factory-tour-photo rise" style="--delay: 420ms" :src="$site->image('factory_tour')" :caption="$site->t('factory.hero.tour_caption')">
                <span class="chip media__badge factory-tour-photo__badge">{{ $site->t('factory.hero.tour') }}</span>
            </x-site.media>
        </div>
    </section>

    {{-- INTER ROLL × IMPOL GROUP: where the experience comes from --}}
    <section class="section section--line-bottom factory-group" x-reveal>
        <div class="container stack stack--l">
            <div class="split split--end" data-reveal>
                <div class="stack stack--s split__wide" style="--split-basis: 560px">
                    <p class="eyebrow">{{ $site->t('factory.group.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('factory.group.title') }}</h2>
                    <p class="lead">{{ $site->t('factory.group.lead') }}</p>
                </div>
                <div class="factory-group__since" style="--split-basis: 220px">
                    <b class="factory-group__year">{{ $site->t('factory.group.since') }}</b>
                    <span class="label">{{ $site->t('factory.group.since_label') }}</span>
                </div>
            </div>

            <div class="grid grid--fit" style="--grid-min: 320px">
                @foreach (['vision', 'mission'] as $part)
                    <article class="panel panel--pad stack stack--s" data-reveal style="--delay: {{ 120 + $loop->index * 120 }}ms">
                        <h3 class="h3">{{ $site->t("factory.group.{$part}_title") }}</h3>
                        <p class="body-text">{{ $site->t("factory.group.{$part}") }}</p>
                    </article>
                @endforeach
            </div>

            <div class="factory-group__photos" data-reveal style="--delay: 300ms">
                @foreach ($groupPhotos as [$key, $fallback])
                    <figure class="factory-group__photo">
                        <img src="{{ $site->image($key) ?? asset($fallback) }}" alt="{{ $site->t('factory.group.photos')[$loop->index] }}" loading="lazy" decoding="async">
                        <figcaption class="meta">{{ $site->t('factory.group.photos')[$loop->index] }}</figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="stack stack--s" data-reveal style="--delay: 400ms">
                <p class="label">{{ $site->t('factory.group.export_title') }}</p>
                <ul class="chips factory-group__countries" role="list">
                    @foreach ($site->t('factory.group.countries') as $country)<li class="chip">{{ $country }}</li>@endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- Virtual tour: plan of the workshops --}}
    <section class="section section--tint section--line-bottom" x-reveal x-data="{ zone: 0 }">
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('factory.tour.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('factory.tour.title') }}</h2>
                </div>
                <span class="label">{{ $site->t('factory.tour.hint') }}</span>
            </div>
            <div class="split" style="--split-gap: 24px">
                <div class="panel panel--pad stack stack--s split__wide" data-reveal="left" style="--delay: 150ms; --split-basis: 620px">
                    <div class="cluster cluster--between label"><span>{{ $site->t('factory.tour.plan') }}</span><span>{{ $site->t('factory.tour.entrance') }}</span></div>
                    <div class="factory-plan">
                        @foreach ($zones as $i => $zone)
                            <button type="button" class="factory-plan__zone" style="grid-area: {{ $zoneAreas[$i] }}" :aria-pressed="zone === {{ $i }}" @click="zone = {{ $i }}">
                                <span class="mono">{{ sprintf('%02d', $i + 1) }}</span><span>{{ $zone['title'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="cluster label" style="--cluster-gap: 6px 16px"><span>{{ $site->t('factory.tour.legend_flow') }}</span><span>{{ $site->t('factory.tour.legend_selected') }}</span></div>
                </div>

                <div class="panel panel--dark factory-zone" data-reveal style="--delay: 300ms; --split-basis: 380px">
                    @foreach ($zones as $i => $zone)
                        <div x-show="zone === {{ $i }}" @if ($i) x-cloak @endif>
                            <x-site.media class="factory-zone__photo" :src="$site->image('factory_zone_'.($i + 1))" :caption="$zone['photo']" :alt="$zone['title']" />
                            <div class="stack stack--m factory-zone__body">
                                <span class="eyebrow text-accent">{{ $site->t('factory.tour.zone', ['n' => sprintf('%02d', $i + 1)]) }}</span>
                                <span class="h3">{{ $zone['title'] }}</span>
                                <span class="factory-zone__text">{{ $zone['text'] }}</span>
                                <ul class="mono text-small factory-zone__equipment">
                                    @foreach ($zone['equipment'] as $item)<li>— {{ $item }}</li>@endforeach
                                </ul>
                                <div class="cluster" style="--cluster-gap: 8px">
                                    <button type="button" class="btn btn--square btn--ghost-dark" aria-label="{{ $site->t('factory.tour.prev') }}" @click="zone = (zone + {{ count($zones) - 1 }}) % {{ count($zones) }}">←</button>
                                    <button type="button" class="btn btn--accent factory-zone__next" @click="zone = (zone + 1) % {{ count($zones) }}">{{ $site->t('factory.tour.next') }}</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Six guarantees --}}
    <section class="section" x-reveal>
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('factory.pillars.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('factory.pillars.title') }}</h2>
                </div>
            </div>
            <div class="grid grid--fit" style="--grid-min: 300px">
                @foreach ($site->t('factory.pillars.items') as $pillar)
                    <div class="card card--hover pillar" data-reveal style="--delay: {{ 120 + $loop->index * 100 }}ms">
                        <span class="cluster cluster--between"><span class="icon-tile"><x-site.icon :path="$pillarIcons[$loop->index]" /></span><span class="meta">{{ sprintf('/%02d', $loop->iteration) }}</span></span>
                        <span class="h3">{{ $pillar['title'] }}</span>
                        <span class="body-text">{{ $pillar['text'] }}</span>
                        <span class="pillar__proof mono">{{ $pillar['proof'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Import vs local --}}
    <section class="section section--dark" x-reveal>
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow text-accent">{{ $site->t('factory.compare.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('factory.compare.title') }}</h2>
                </div>
            </div>
            <div class="compare-table" data-reveal="scale" style="--delay: 150ms">
                <table>
                    <thead class="mono">
                        <tr><th>{{ $site->t('factory.compare.criterion') }}</th><th>{{ $site->t('factory.compare.import') }}</th><th class="compare-table__us">{{ $site->t('factory.compare.us') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($site->t('factory.compare.rows') as $row)
                            <tr><td>{{ $row['k'] }}</td><td class="compare-table__them">{{ $row['a'] }}</td><td class="compare-table__ours">✓ {{ $row['b'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Quality control --}}
    <section class="section section--line-bottom" x-reveal>
        <div class="container split split--center">
            <div class="stack stack--m" data-reveal="left" style="--split-basis: 420px">
                <p class="eyebrow">{{ $site->t('factory.quality.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('factory.quality.title') }}</h2>
                <p class="body-text">{{ $site->t('factory.quality.text') }}</p>
                <ul class="check-list">
                    @foreach ($site->t('factory.quality.checks') as $check)<li class="check-list__item">{{ $check }}</li>@endforeach
                </ul>
            </div>
            <div class="protocol-wrap" data-reveal="scale" style="--delay: 200ms; --split-basis: 460px">
                <div class="protocol mono">
                    <div class="protocol__head"><b>{{ $site->t('factory.quality.protocol') }}</b><span>INTER ROLL</span></div>
                    <dl class="protocol__meta">
                        <dt>{{ $site->t('factory.quality.part') }}</dt><dd>{{ $site->t('factory.quality.part_value') }}</dd>
                        <dt>{{ $site->t('factory.quality.batch') }}</dt><dd>{{ $site->t('factory.quality.batch_value') }}</dd>
                        <dt>{{ $site->t('factory.quality.date') }}</dt><dd>{{ $site->setting('protocol_date') }}</dd>
                    </dl>
                    <table class="protocol__table">
                        <tbody>
                            @foreach ($site->t('factory.quality.rows') as [$name, $value])
                                <tr><td>{{ $name }}</td><td>{{ $value }}</td><td><b>{{ $site->t('factory.quality.passed') }}</b></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="protocol__foot"><span>{{ $site->t('factory.quality.inspector') }}</span><span class="protocol__stamp">{{ $site->t('factory.quality.accepted') }}</span></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Before / after --}}
    <section class="section section--tint section--line-bottom" x-reveal>
        <div class="container split split--center">
            <div class="stack" data-reveal="left" style="--split-basis: 360px">
                <p class="eyebrow">{{ $site->t('factory.result.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('factory.result.title') }}</h2>
                <p class="body-text">{{ $site->t('factory.result.text') }}</p>
                <span class="mono text-small factory-saving">{{ $site->t('factory.result.saving') }} <b class="hl">{{ $site->t('factory.result.saving_value') }}</b> {{ $site->t('factory.result.term') }}</span>
            </div>
            <x-site.compare class="split__wide" data-reveal style="--delay: 200ms; --split-basis: 620px"
                :before="$site->image('factory_before')" :after="$site->image('factory_after')"
                :before-caption="$site->t('factory.result.before')" :after-caption="$site->t('factory.result.after')" />
        </div>
    </section>

    {{-- Clients, reviews, certificates (from the admin; placeholders until filled) --}}
    <section class="section" x-reveal>
        <div class="container stack stack--l">
            <div class="section-head__main" data-reveal>
                <p class="eyebrow">{{ $site->t('factory.trust.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('factory.trust.title') }}</h2>
            </div>

            <div class="panel client-logos" data-reveal style="--delay: 150ms">
                @forelse ($clients as $client)
                    <div class="client-logos__item">@if ($client->logo)<img src="{{ $client->logo }}" alt="{{ $client->name }}" loading="lazy">@else{{ $client->name }}@endif</div>
                @empty
                    @for ($i = 0; $i < 6; $i++)<div class="client-logos__item">{{ $site->t('factory.trust.logo') }}</div>@endfor
                @endforelse
            </div>

            @php($quotes = $testimonials ?: array_map(fn ($q) => new \App\Services\Site\ViewModels\Testimonial($q['text'], $q['author'], null), $site->t('factory.trust.quotes')))
            <div class="grid grid--fit" style="--grid-min: 340px">
                @foreach ($quotes as $quote)
                    <figure class="panel panel--pad stack stack--m" data-reveal style="--delay: {{ 250 + $loop->index * 120 }}ms">
                        <blockquote class="review__text">“{{ $quote->text }}”</blockquote>
                        <figcaption class="cluster label" style="--cluster-gap: 12px">
                            <x-site.media class="review__photo" :src="$quote->photo" />{{ $quote->author }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            @php($certs = $certificates ?: array_map(fn ($c) => new \App\Services\Site\ViewModels\Certificate($c['title'], $c['details'], null, null), $site->t('factory.trust.certificates')))
            <div class="cluster" data-reveal style="--cluster-gap: 16px; --delay: 450ms">
                @foreach ($certs as $cert)
                    <a @if ($cert->url) href="{{ $cert->url }}" target="_blank" rel="noopener" @endif class="card card--hover certificate">
                        <x-site.media class="certificate__preview" :src="$cert->preview" />
                        <span class="stack stack--xs"><b>{{ $cert->title }}</b><span class="meta">{{ $cert->details }}</span></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--tint section--line-top section--line-bottom">
        <div class="container split">
            <div class="stack" style="--split-basis: 320px">
                <p class="eyebrow">{{ $site->t('factory.faq.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('factory.faq.title') }}</h2>
            </div>
            <x-site.faq class="split__wide" style="--split-basis: 560px" :items="$site->t('factory.faq.items')" />
        </div>
    </section>

    <section id="request" class="section section--accent section--line-bottom">
        <div class="container cluster cluster--between" style="--cluster-gap: 32px">
            <div class="stack factory-visit">
                <p class="eyebrow">{{ $site->t('factory.visit.eyebrow') }}</p>
                <h2 class="cta-band__title">{{ $site->t('factory.visit.title') }}</h2>
                <p class="lead text-ink">{{ $site->t('factory.visit.text') }}</p>
            </div>
            <div class="stack stack--s factory-visit__actions">
                <a href="{{ $site->url('contact') }}#request" class="btn btn--dark btn--lg">{{ $site->t('factory.visit.plan') }}</a>
                <a href="{{ $site->tel() }}" class="btn btn--lg btn--mono">{{ $site->setting('phone') }}</a>
                <a href="{{ $site->map('map_yandex') }}" target="_blank" rel="noopener" class="btn btn--lg btn--mono">{{ $site->t('factory.visit.map') }}</a>
            </div>
        </div>
    </section>
</x-site.layout>
