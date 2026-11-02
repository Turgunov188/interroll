{{-- Contacts: channels, live opening status, directions, departments, the contact form, requisites. --}}
@php
    $channels = [
        'phone' => ['value' => $site->setting('phone'), 'href' => $site->tel(), 'icon' => 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z'],
        'telegram' => ['value' => '@'.$site->setting('telegram'), 'href' => 'https://t.me/'.$site->setting('telegram'), 'icon' => 'M22 2L11 13 M22 2l-7 20-4-9-9-4z'],
        'email' => ['value' => $site->setting('email'), 'href' => 'mailto:'.$site->setting('email'), 'icon' => 'M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z M22 6l-10 7L2 6'],
        'address' => ['value' => $site->setting('address'), 'href' => $site->map('map_yandex'), 'icon' => 'M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'],
    ];
    $open = max(0, min(23, $site->number('open_hour') ?: 8));
    $close = max(1, min(24, $site->number('close_hour') ?: 18));
    $status = ['open' => $open, 'close' => $close, 'texts' => $site->t('contact.status')];
    $topics = $site->t('contact.form.topics');
    $replies = $site->t('contact.form.replies');
    $requisites = $site->t('contact.requisites.rows');
@endphp
<x-site.layout :title="$site->t('meta.contact.title')" :description="$site->t('meta.contact.description')">

    <section class="section--hero">
        <div class="container stack stack--l">
            <x-site.breadcrumbs class="rise" :items="[$site->t('common.home') => $site->url('index'), $site->t('contact.breadcrumb') => null]" />
            <div class="cluster cluster--between cluster--end" style="--cluster-gap: 32px">
                <x-site.title tag="h1" class="display rise" style="--delay: 80ms" :lines="[$site->t('contact.title')]" :accent="$site->t('contact.accent')" />
                <div class="panel panel--pad stack stack--s mono contact-status rise" style="--delay: 200ms" x-data="openingStatus(@js($status))">
                    <span class="cluster contact-status__title" style="--cluster-gap: 10px"><span class="contact-status__dot" :style="{ background: dot }" aria-hidden="true"></span><span x-text="title">{{ $site->t('contact.status.hours') }}</span></span>
                    <span class="text-soft" x-text="subtitle">{{ sprintf('%02d:00 – %02d:00', $open, $close) }}</span>
                    <span class="meta" x-text="clockLine"></span>
                </div>
            </div>

            <div class="grid grid--fit" style="--grid-min: 260px">
                @foreach ($channels as $key => $channel)
                    @php($text = $site->t("contact.channels.{$key}"))
                    <div class="card card--hover contact-channel rise @if ($loop->first) contact-channel--hot @endif" style="--delay: {{ 260 + $loop->index * 90 }}ms" x-data="copyButton(@js($channel['value']))">
                        <span class="cluster cluster--between"><span @class(['icon-tile', 'contact-channel__icon--plain' => ! $loop->first])><x-site.icon :path="$channel['icon']" :size="26" /></span><span class="label">{{ $text['tag'] }}</span></span>
                        <span class="label">{{ $text['label'] }}</span>
                        <a href="{{ $channel['href'] }}" class="contact-channel__value" @if ($key === 'telegram' || $key === 'address') target="_blank" rel="noopener" @endif>{{ $channel['value'] }}</a>
                        <span class="body-text text-small">{{ $text['text'] }}</span>
                        <span class="cluster contact-channel__actions" style="--cluster-gap: 8px">
                            <a href="{{ $channel['href'] }}" class="btn btn--dark btn--mono btn--sm" @if ($key === 'telegram' || $key === 'address') target="_blank" rel="noopener" @endif>{{ $text['action'] }}</a>
                            <button type="button" class="btn btn--mono btn--sm" aria-label="{{ $site->t('contact.copy_label', ['channel' => $text['label']]) }}" @click="copy()"
                                    x-text="copied ? @js($site->t('contact.copied')) : @js($site->t('contact.copy'))">{{ $site->t('contact.copy') }}</button>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Directions --}}
    <section class="section section--tight section--tint section--line-bottom">
        <div class="container split" style="--split-gap: 24px">
            <div class="panel split__wide contact-map" style="--split-basis: 640px">
                <div class="panel__head"><span>{{ $site->t('contact.map.title') }}</span><span>{{ $site->setting('city_district') }}</span></div>
                <svg role="img" aria-label="{{ $site->t('contact.map.label') }}" viewBox="0 0 720 400" width="100%" class="contact-map__scheme">
                    <g stroke="#dde1e5" stroke-width="1">
                        <path d="M0 40h720M0 80h720M0 120h720M0 160h720M0 200h720M0 240h720M0 280h720M0 320h720M0 360h720"/>
                        <path d="M40 0v400M80 0v400M120 0v400M160 0v400M200 0v400M240 0v400M280 0v400M320 0v400M360 0v400M400 0v400M440 0v400M480 0v400M520 0v400M560 0v400M600 0v400M640 0v400M680 0v400"/>
                    </g>
                    <path d="M0 300 C160 290 260 250 380 250 S600 230 720 210" stroke="#c9ced4" stroke-width="26" fill="none"/>
                    <path d="M0 300 C160 290 260 250 380 250 S600 230 720 210" stroke="#fff" stroke-width="2" fill="none" stroke-dasharray="14 10"/>
                    <path d="M440 0 L470 400" stroke="#c9ced4" stroke-width="18" fill="none"/>
                    <path d="M120 0 C140 120 180 200 300 250" stroke="#d6dadf" stroke-width="12" fill="none"/>
                    <rect x="500" y="80" width="120" height="70" fill="#e3e7eb" stroke="#b9c0c8"/>
                    <rect x="250" y="70" width="140" height="80" fill="#e3e7eb" stroke="#b9c0c8"/>
                    <rect x="80" y="320" width="160" height="60" fill="#e3e7eb" stroke="#b9c0c8"/>
                    <path class="contact-map__route" d="M60 296 C160 290 260 252 380 252 S455 240 458 200 L462 150" stroke="#111418" stroke-width="3" fill="none"/>
                    <rect x="430" y="96" width="70" height="52" class="contact-map__factory" stroke="#111418" stroke-width="2"/>
                    <circle class="contact-map__ring" cx="465" cy="122" r="10"/>
                    <circle cx="465" cy="122" r="7" fill="#111418"/>
                    <g font-family="JetBrains Mono, monospace" font-size="12" fill="#111418">
                        <text x="512" y="70">{{ $site->t('contact.map.factory') }}</text>
                        <text x="512" y="86" fill="#5b636d">{{ $site->setting('street') }}</text>
                        <text x="14" y="288">{{ $site->setting('main_road') }}</text>
                        <text x="476" y="390">{{ $site->setting('side_street') }}</text>
                        <text x="16" y="312" fill="#5b636d">● {{ $site->setting('transit_stop') }}</text>
                    </g>
                </svg>
                <div class="contact-map__links mono">
                    <a href="{{ $site->map('map_yandex') }}" target="_blank" rel="noopener">{{ $site->t('contact.map.yandex') }}</a>
                    <a href="{{ $site->map('map_google') }}" target="_blank" rel="noopener">{{ $site->t('contact.map.google') }}</a>
                    <a href="{{ $site->map('map_route') }}" target="_blank" rel="noopener">{{ $site->t('contact.map.route') }}</a>
                </div>
            </div>
            <div class="stack" style="--split-basis: 340px">
                <div class="panel panel--dark panel--pad stack stack--s">
                    <span class="eyebrow text-accent">{{ $site->t('contact.address.title') }}</span>
                    <span class="h3">{{ $site->setting('address') }}</span>
                    <span class="mono text-small text-soft">{{ $site->t('contact.address.details') }}<br>{{ $site->t('contact.address.trucks') }}</span>
                </div>
                <div class="panel panel--pad stack stack--s" x-data="openingStatus(@js($status))">
                    <span class="label">{{ $site->t('contact.hours.title') }}</span>
                    <span class="contact-hours__row" :class="{ 'is-today': isWeekday }"><span>{{ $site->t('contact.hours.weekdays') }}</span><span class="mono">{{ sprintf('%02d:00 – %02d:00', $open, $close) }}</span></span>
                    <span class="contact-hours__row" :class="{ 'is-today': day === 'Sat' }"><span>{{ $site->t('contact.hours.saturday') }}</span><span class="mono">{{ sprintf('%02d:00', $open) }} – {{ $site->setting('saturday_close_hour') }}:00</span></span>
                    <span class="contact-hours__row" :class="{ 'is-today': day === 'Sun' }"><span>{{ $site->t('contact.hours.sunday') }}</span><span class="mono">{{ $site->t('contact.hours.day_off') }}</span></span>
                </div>
                <div class="panel panel--pad stack stack--s">
                    <span class="label">{{ $site->t('contact.route.title') }}</span>
                    <span class="body-text text-ink">{{ $site->t('contact.route.transit') }}<br>{{ $site->t('contact.route.car') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Departments --}}
    <section class="section section--tight section--line-bottom">
        <div class="container">
            <div class="section-head">
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('contact.people.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('contact.people.title') }}</h2>
                </div>
            </div>
            <div class="grid grid--fit" style="--grid-min: 280px">
                @foreach ($site->t('contact.people.items') as $person)
                    <div class="card card--hover">
                        <div class="cluster contact-person__head" style="--cluster-gap: 16px">
                            <x-site.media class="contact-person__photo" />
                            <span class="stack stack--xs"><b class="contact-person__name">{{ $person['name'] }}</b><span class="label">{{ $person['role'] }}</span></span>
                        </div>
                        <div class="card__body">
                            <span class="body-text">{{ $person['text'] }}</span>
                            <a href="{{ $site->tel($person['phone']) }}" class="mono contact-person__phone">{{ $site->setting($person['phone']) }}</a>
                            <span class="meta">{{ $site->t('contact.people.languages', ['languages' => $person['languages']]) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Contact form --}}
    <section id="request" class="section section--accent section--line-bottom">
        <div class="container split" x-data="contactForm(@js(['topics' => $topics, 'replies' => $replies, 'sent' => $site->t('contact.form.sent_text')]))">
            <div class="stack" style="--split-basis: 360px">
                <p class="eyebrow">{{ $site->t('contact.form.eyebrow') }}</p>
                <h2 class="cta-band__title">{{ $site->t('contact.form.title') }}</h2>
                <p class="lead text-ink">{{ $site->t('contact.form.text') }}</p>
                <div class="mono text-small stack stack--s contact-promises">
                    @foreach ($site->t('contact.form.promises') as $promise)<span>{{ $promise }}</span>@endforeach
                </div>
            </div>
            <x-site.lead-form type="contact" form-class="lead-form lead-form--column" style="--split-basis: 540px">
                <input type="hidden" name="details[topic]" :value="topic">
                <input type="hidden" name="details[reply_via]" :value="reply">
                <fieldset class="stack stack--s">
                    <legend class="label contact-form__legend">{{ $site->t('contact.form.topic') }}</legend>
                    <div class="grid" style="--grid-min: 150px; --grid-gap: 8px">
                        @foreach ($topics as $key => $topic)
                            <button type="button" class="btn btn--sm contact-form__choice" :class="{ 'is-active': topic === @js($key) }" :aria-pressed="topic === @js($key)" @click="topic = @js($key)">{{ $topic['label'] }}</button>
                        @endforeach
                    </div>
                </fieldset>
                <div class="grid" style="--grid-min: 200px; --grid-gap: 16px">
                    <x-site.field name="name" :label="$site->t('common.form.name')" :placeholder="$site->t('common.form.your_name')" autocomplete="name" required />
                    <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
                </div>
                <x-site.field name="company" :label="$site->t('common.form.company')" :placeholder="$site->t('common.form.company_placeholder')" autocomplete="organization" />
                <x-site.field name="message" textarea rows="3" :label="$site->t('common.form.message')" x-bind:placeholder="hint" />
                <x-site.dropzone :label="$site->t('common.form.file_any')" />
                <fieldset class="stack stack--s">
                    <legend class="label contact-form__legend">{{ $site->t('contact.form.reply') }}</legend>
                    <div class="segmented segmented--grid" style="--cols: 3">
                        @foreach ($replies as $key => $label)
                            <button type="button" class="segmented__item" :aria-pressed="reply === @js($key)" @click="reply = @js($key)">{{ $label }}</button>
                        @endforeach
                    </div>
                </fieldset>
                <x-site.submit :label="$site->t('common.form.send_application')" />

                <x-slot:success>
                    <span class="success__title">{{ $site->t('contact.form.sent_title') }}</span>
                    <span class="body-text" x-text="sentText"></span>
                    <span class="mono text-small" x-show="number">{{ $site->t('common.form.request_number') }}: <span x-text="number"></span></span>
                    <button type="button" class="btn btn--mono" @click="again()">{{ $site->t('contact.form.again') }}</button>
                </x-slot:success>
            </x-site.lead-form>
        </div>
    </section>

    {{-- Requisites --}}
    @php($requisitesText = collect($requisites)->map(fn ($row) => $row[0].': '.$row[1])->implode("\n"))
    <section class="section section--tight">
        <div class="container split split--start">
            <div class="stack" style="--split-basis: 320px">
                <p class="eyebrow">{{ $site->t('contact.requisites.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('contact.requisites.title') }}</h2>
                <p class="body-text">{{ $site->t('contact.requisites.text') }}</p>
            </div>
            <div class="panel split__wide" style="--split-basis: 560px" x-data="copyButton(@js($requisitesText))">
                <dl class="requisites mono">
                    @foreach ($requisites as [$label, $value])
                        <dt>{{ $label }}</dt><dd>{{ $value }}</dd>
                    @endforeach
                </dl>
                <button type="button" class="requisites__copy mono" :class="{ 'is-copied': copied }" @click="copy()"
                        x-text="copied ? @js($site->t('contact.requisites.copied')) : @js($site->t('contact.requisites.copy'))">{{ $site->t('contact.requisites.copy') }}</button>
            </div>
        </div>
    </section>
</x-site.layout>
