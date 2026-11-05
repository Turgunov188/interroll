{{-- Home page. Data: App\Services\Site\PageData\HomePageData ($industries, $categories). --}}
<x-site.layout :title="$site->t('meta.index.title')" :description="$site->t('meta.index.description')" :brand="false" cta="#request">

    {{-- Hero with the turning roller --}}
    <section class="home-hero" x-data="heroRoller" @mousemove="move($event)" @mouseleave="reset()">
        <div class="home-hero__glow" aria-hidden="true"></div>
        <div class="home-hero__model" aria-hidden="true">
            <div class="home-hero__roller" x-ref="roller">
                <div class="home-hero__tilt" x-ref="tilt">
                    <div class="spin-x home-hero__spin" x-ref="spin"></div>
                </div>
                <div class="home-hero__shadow"></div>
                <div class="home-hero__caption mono">{{ $site->t('home.hero.model_caption') }}</div>
            </div>
        </div>

        <div class="container home-hero__inner">
            <div class="home-hero__text stack stack--l" x-ref="text">
                <p class="eyebrow rise">{{ $site->t('home.hero.eyebrow') }}</p>
                <x-site.title tag="h1" class="display home-hero__title rise" style="--delay: 120ms" :lines="[$site->t('home.hero.title')]" :accent="$site->t('home.hero.accent')" />
                <p class="lead rise" style="--delay: 240ms">{{ $site->t('home.hero.lead') }}</p>
                <div class="cluster rise" style="--delay: 360ms">
                    <a href="{{ $site->url('catalog') }}" class="btn btn--dark btn--lg">{{ $site->t('home.hero.catalog') }}</a>
                    <a href="#request" class="btn btn--lg">{{ $site->t('home.hero.price') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Production limits from the settings --}}
    @php($marquee = $site->t('home.marquee.items'))
    <section class="marquee" aria-label="{{ $site->t('home.marquee.label') }}">
        <div class="marquee__track">
            @foreach ([false, true] as $copy)
                @foreach ($marquee as $item)
                    <span @if ($copy) aria-hidden="true" @endif>{{ $item }}</span><span class="marquee__dot" aria-hidden="true">●</span>
                @endforeach
            @endforeach
        </div>
    </section>

    {{-- What we make: particle drawing + five steps --}}
    @php($steps = $site->t('home.story.steps'))
    <section class="home-story" aria-label="{{ $site->t('home.story.label') }}"
             x-data="productStory({ count: {{ count($steps) }} })" @mouseenter="hold = true" @mouseleave="hold = false">
        <canvas class="home-story__canvas" x-ref="canvas" aria-hidden="true"></canvas>
        <div class="container home-story__inner">
            <div class="home-story__chip mono" aria-live="polite">
                <span class="home-story__dot" aria-hidden="true"></span>
                @foreach ($steps as $i => $step)
                    <span x-show="step === {{ $i }}" @if ($i) x-cloak @endif>{{ $step['chip'] }} · {{ sprintf('%02d/%02d', $i + 1, count($steps)) }}</span>
                @endforeach
            </div>

            <div class="home-story__panel stack">
                <p class="eyebrow">{{ $site->t('home.story.eyebrow') }}</p>
                <div class="home-story__counter">
                    <span class="home-story__number" x-text="String(step + 1).padStart(2, '0')">01</span>
                    <span class="home-story__bar" aria-hidden="true"><span :style="{ animationPlayState: hold ? 'paused' : 'running' }" x-ref="bar"></span></span>
                    <span class="mono text-muted">/ {{ sprintf('%02d', count($steps)) }}</span>
                </div>
                <div class="home-story__steps">
                    @foreach ($steps as $i => $step)
                        <div class="home-story__step stack" :class="stepClass({{ $i }})" :aria-hidden="step !== {{ $i }}">
                            <h3 class="home-story__title">{{ $step['title'] }}</h3>
                            <p class="body-text">{{ $step['text'] }}</p>
                            <span class="bar-note mono home-story__spec">{{ $step['spec'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="cluster home-story__controls" style="--cluster-gap: 8px">
                    <button type="button" class="btn btn--square" aria-label="{{ $site->t('home.story.prev') }}" @click="go(step - 1)">←</button>
                    @foreach ($steps as $i => $step)
                        <button type="button" class="home-story__dot-btn mono" :aria-pressed="step === {{ $i }}" aria-label="{{ $step['short'] }}" @click="go({{ $i }})">{{ sprintf('%02d', $i + 1) }}</button>
                    @endforeach
                    <button type="button" class="btn btn--square btn--dark" aria-label="{{ $site->t('home.story.next') }}" @click="go(step + 1)">→</button>
                </div>
            </div>
        </div>
    </section>

    {{-- Products by industry --}}
    @if ($industries)
        <section class="section" x-reveal x-data="{ current: @js($industries[0]->slug) }">
            <div class="container stack stack--l">
                <p class="eyebrow" data-reveal>{{ $site->t('home.industries.eyebrow') }}</p>
                <h2 class="h2" data-reveal style="--delay: 100ms">{{ $site->t('home.industries.title') }}</h2>
                <div class="segmented segmented--industries" role="tablist" data-reveal="right" style="--delay: 200ms">
                    @foreach ($industries as $industry)
                        <button type="button" role="tab" class="segmented__item" id="tab-{{ $industry->slug }}" aria-controls="panel-{{ $industry->slug }}"
                                :aria-selected="current === @js($industry->slug)" @click="current = @js($industry->slug)">{{ $industry->name }}</button>
                    @endforeach
                </div>
                @foreach ($industries as $industry)
                    <div class="grid grid--fit" role="tabpanel" id="panel-{{ $industry->slug }}" aria-labelledby="tab-{{ $industry->slug }}"
                         x-show="current === @js($industry->slug)" @if (! $loop->first) x-cloak @endif style="--grid-min: 260px">
                        @foreach ($industry->products as $product)
                            <x-site.product-card :product="$product" class="card-in" style="--delay: {{ $loop->index * 90 }}ms" />
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Catalog sections --}}
    @if ($categories)
        <section class="section section--tint section--line-top section--line-bottom" x-reveal>
            <div class="container stack stack--l">
                <p class="eyebrow" data-reveal>{{ $site->t('home.sections.eyebrow') }}</p>
                <div class="row-links">
                    @foreach ($categories as $category)
                        <a href="{{ $category->url }}" class="row-link" data-reveal="left" style="--delay: {{ 120 + $loop->index * 90 }}ms">
                            <span class="row-link__number mono">{{ $category->number }}</span>
                            <span class="row-link__title">{{ $category->name }}</span>
                            <span class="row-link__text">{{ $category->description }}</span>
                            <span class="row-link__arrow" aria-hidden="true">→</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Recoating teaser --}}
    <section class="section" x-reveal>
        <div class="container split">
            <div class="stack stack--m" style="--split-basis: 380px">
                <p class="eyebrow" data-reveal>{{ $site->t('home.recoating.eyebrow') }}</p>
                <h2 class="h2" data-reveal style="--delay: 100ms">{{ $site->t('home.recoating.title') }}</h2>
                <p class="body-text" data-reveal style="--delay: 200ms">{{ $site->t('home.recoating.text') }}</p>
                <ol class="stack stack--s mono text-small">
                    @foreach ($site->t('home.recoating.steps') as $step)
                        <li class="bar-note" data-reveal="left" style="--delay: {{ 320 + $loop->index * 100 }}ms">{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
            <div class="home-before-after split__wide">
                <x-site.media data-reveal="clip" style="--delay: 200ms" :src="$site->image('home_before')" :caption="$site->t('home.recoating.before')" art="worn" />
                <x-site.media data-reveal="clip-up" style="--delay: 650ms" :src="$site->image('home_after')" :caption="$site->t('home.recoating.after')" art="restored" />
            </div>
        </div>
    </section>

    {{-- Price request --}}
    <section id="request" class="section section--accent section--line-top" x-reveal>
        <div class="container split">
            <div class="stack" style="--split-basis: 380px">
                <p class="eyebrow" data-reveal>{{ $site->t('home.request.eyebrow') }}</p>
                <h2 class="h2" data-reveal style="--delay: 120ms">{{ $site->t('home.request.title') }}</h2>
                <p class="mono text-small" data-reveal style="--delay: 240ms">{{ $site->t('home.request.contacts') }}</p>
            </div>
            <x-site.lead-form type="quote" data-reveal="scale" style="--delay: 200ms; --split-basis: 520px">
                <x-site.field name="name" :label="$site->t('common.form.company_name')" :placeholder="$site->t('common.form.name_placeholder')" autocomplete="name" />
                <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
                <x-site.dropzone :label="$site->t('common.form.file')" :hint="$site->t('common.form.drop')" />
                <x-site.field name="message" textarea rows="2" full :label="$site->t('common.form.comment')" :placeholder="$site->t('home.request.comment_placeholder')" />
                <x-site.submit :label="$site->t('common.form.send_request')" />
            </x-site.lead-form>
        </div>
    </section>

</x-site.layout>
