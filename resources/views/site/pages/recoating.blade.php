{{-- Recoating service: live process scheme, savings calculator, materials, before/after, request. --}}
@php
    $materials = config('site.materials');
    $stages = $site->t('recoating.process.stages');
    $config = [
        'materials' => collect($materials)->map(fn ($m, $key) => ['name' => $site->t("common.materials.{$key}"), 'color' => $m['color']])->all(),
        'texts' => [
            'worn' => $site->t('recoating.process.worn'),
            'stripped' => $site->t('recoating.process.stripped'),
            'newLayer' => $site->t('recoating.process.new_layer'),
            'label' => $site->t('recoating.process.label'),
            'stages' => $stages,
        ],
    ];
@endphp
<x-site.layout :title="$site->t('meta.recoating.title')" :description="$site->t('meta.recoating.description')" cta="#request">
<div x-data="recoating(@js($config))">

    <section class="section--hero">
        <div class="container split split--center">
            <div class="stack stack--m" style="--split-basis: 520px">
                <x-site.breadcrumbs class="rise" :items="[$site->t('common.home') => $site->url('index'), $site->t('recoating.breadcrumb_services') => null, $site->t('recoating.breadcrumb') => null]" />
                <x-site.title tag="h1" class="display rise" style="--delay: 80ms" :lines="$site->t('recoating.hero.lines')" :accent="$site->t('recoating.hero.accent')" />
                <p class="lead rise" style="--delay: 180ms">{{ $site->t('recoating.hero.lead') }}</p>
                <div class="cluster rise" style="--delay: 260ms">
                    <a href="#request" class="btn btn--dark btn--lg">{{ $site->t('recoating.hero.send_photo') }}</a>
                    <a href="#savings" class="btn btn--lg">{{ $site->t('recoating.hero.savings') }}</a>
                </div>
            </div>

            {{-- The four stages, animated --}}
            <figure class="panel process rise" style="--delay: 200ms; --split-basis: 520px" @mouseenter="hold = true">
                <div class="panel__head"><span>{{ $site->t('recoating.process.title') }}</span><span x-text="String(stage + 1).padStart(2, '0') + ' / 04'"></span></div>
                <svg class="process__scheme blueprint" role="img" :aria-label="stageLabel" viewBox="0 0 560 300" width="100%">
                    <defs><pattern id="process-core" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="8" height="8" fill="#d2d7dc"/><line x1="0" y1="0" x2="0" y2="8" stroke="#8a939d" stroke-width="2"/></pattern></defs>
                    <line x1="20" y1="150" x2="540" y2="150" stroke="#111418" stroke-dasharray="14 4 2 4"/>
                    <rect x="40" y="136" width="480" height="28" fill="#4a535e" stroke="#111418" stroke-width="1.5"/>
                    <rect class="process__anim" x="100" width="360" :y="coat.y" :height="coat.height" :fill="coat.fill" :fill-opacity="coat.opacity" stroke="#111418" stroke-width="2"/>
                    <rect x="100" y="112" width="360" height="76" fill="url(#process-core)" stroke="#111418" stroke-width="1.5"/>
                    <g class="process__anim" :style="{ opacity: stage === 0 ? 1 : 0 }" stroke="#111418" stroke-width="1.5" fill="none">
                        <path d="M130 90l14 8-6 10 M210 92l10 10 M300 88l8 12-10 6 M380 94l12 6 M170 206l10-8 M260 210l14-10 M350 206l8-8"/>
                    </g>
                    <g class="process__anim" :style="{ opacity: stage === 2 ? 1 : 0 }">
                        <rect x="262" y="20" width="36" height="40" fill="#fff" stroke="#111418" stroke-width="1.5"/>
                        <rect class="process__anim" x="276" y="60" width="8" :height="stage === 2 ? 22 : 0" :fill="materialColor" stroke="#111418"/>
                    </g>
                    <g class="process__anim" :style="{ opacity: stage === 3 ? 1 : 0 }">
                        <rect class="process__anim" :x="stage === 3 ? 360 : 120" y="56" width="70" height="26" fill="#fff" stroke="#111418" stroke-width="1.5"/>
                        <circle class="process__anim" :cx="stage === 3 ? 395 : 155" cy="82" r="12" fill="#d2d7dc" stroke="#111418" stroke-width="1.5"/>
                    </g>
                    <g font-family="JetBrains Mono, monospace" font-size="12" fill="#111418">
                        <text x="100" y="286">{{ $site->t('recoating.process.core') }}</text>
                        <text x="460" y="286" text-anchor="end" x-text="coatCaption"></text>
                    </g>
                </svg>
                <figcaption class="process__stages">
                    @foreach ($stages as $i => $title)
                        <button type="button" class="process__stage" :aria-pressed="stage === {{ $i }}" @click="pick({{ $i }})">
                            <span class="mono">{{ sprintf('%02d', $i + 1) }}</span><span>{{ $title }}</span>
                        </button>
                    @endforeach
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Savings calculator --}}
    <section id="savings" class="section section--dark" x-reveal>
        <div class="container split">
            <div class="stack stack--m" data-reveal="left" style="--split-basis: 420px">
                <p class="eyebrow text-accent">{{ $site->t('recoating.calculator.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('recoating.calculator.title') }}</h2>
                <label class="stack stack--s">
                    <span class="label">{{ $site->t('recoating.calculator.price') }}</span>
                    <input type="number" class="input input--dark savings__price" min="0" step="100000" x-model.number="price">
                </label>
                <label class="stack stack--s">
                    <span class="cluster cluster--between"><span class="label">{{ $site->t('recoating.calculator.quantity') }}</span><b><span x-text="quantity"></span> {{ $site->t('common.pcs') }}</b></span>
                    <input type="range" class="range range--accent" min="1" max="50" x-model.number="quantity">
                </label>
                <label class="stack stack--s">
                    <span class="cluster cluster--between"><span class="label">{{ $site->t('recoating.calculator.ratio') }}</span><b x-text="ratio + '%'"></b></span>
                    <input type="range" class="range range--accent" min="20" max="90" x-model.number="ratio">
                </label>
                <p class="meta text-faint">{{ $site->t('recoating.calculator.note') }}</p>
            </div>
            <div class="savings stack stack--m" data-reveal style="--delay: 200ms; --split-basis: 460px">
                <div class="stack stack--s">
                    <div class="cluster cluster--between label"><span>{{ $site->t('recoating.calculator.new') }}</span><span><span x-text="format(newTotal)"></span> {{ $site->t('recoating.calculator.currency') }}</span></div>
                    <div class="savings__track"><div class="savings__bar savings__bar--new"></div></div>
                </div>
                <div class="stack stack--s">
                    <div class="cluster cluster--between label"><span>{{ $site->t('recoating.calculator.recoated') }}</span><span><span x-text="format(recoatTotal)"></span> {{ $site->t('recoating.calculator.currency') }}</span></div>
                    <div class="savings__track"><div class="savings__bar" :style="{ width: ratio + '%' }"></div></div>
                </div>
                <div class="savings__result stack stack--xs">
                    <span class="label">{{ $site->t('recoating.calculator.saving') }}</span>
                    <span class="savings__amount" x-text="format(newTotal - recoatTotal)"></span>
                    <span class="mono text-small" x-text="@js($site->t('recoating.calculator.cheaper')).replace(':percent', 100 - ratio)"></span>
                </div>
                <a href="#request" class="btn btn--accent btn--lg">{{ $site->t('recoating.calculator.action') }}</a>
            </div>
        </div>
    </section>

    {{-- How we work --}}
    <section class="section section--line-bottom" x-reveal>
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('recoating.steps.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('recoating.steps.title') }}</h2>
                </div>
            </div>
            <ol class="panel work-steps">
                @foreach ($site->t('recoating.steps.items') as $step)
                    <li class="work-steps__item stack stack--s" data-reveal style="--delay: {{ 100 + $loop->index * 110 }}ms">
                        <span class="cluster cluster--between"><span class="number work-steps__number">{{ sprintf('%02d', $loop->iteration) }}</span><span class="meta">{{ $step['time'] }}</span></span>
                        <span class="work-steps__title">{{ $step['title'] }}</span>
                        <span class="body-text text-small">{{ $step['text'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Coating materials --}}
    <section class="section section--tint section--line-bottom" x-reveal>
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('recoating.materials.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('recoating.materials.title') }}</h2>
                </div>
                <p class="section-head__aside meta">{{ $site->t('recoating.materials.note') }}</p>
            </div>
            <div class="grid grid--fit" style="--grid-min: 260px">
                @foreach ($site->t('recoating.materials.items') as $key => $item)
                    <button type="button" class="card card--hover material-card" :class="{ 'is-selected': material === @js($key) }" :aria-pressed="material === @js($key)"
                            @click="material = @js($key)" data-reveal style="--delay: {{ 120 + $loop->index * 120 }}ms">
                        <span class="cluster cluster--between"><span class="material-card__title">{{ $site->t("common.materials.{$key}") }}</span><span class="chip">{{ $item['hardness'] }}</span></span>
                        <span class="body-text">{{ $item['text'] }}</span>
                        <span class="stack stack--s material-card__meters">
                            @foreach ($site->t('common.properties') as $property)
                                @php($level = $materials[$key]['levels'][$loop->index])
                                <span class="meter">
                                    <span class="meter__head"><span>{{ $property }}</span><span>{{ $site->t('common.levels')[$level] }}</span></span>
                                    <span class="meter__track"><span class="meter__fill" style="--level: {{ $level }}; --delay: {{ 300 + $loop->parent->index * 120 }}ms"></span></span>
                                </span>
                            @endforeach
                        </span>
                        <span class="material-card__use mono">{{ $item['use'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Before / after --}}
    @php($cases = $site->t('recoating.result.cases'))
    <section class="section" x-reveal x-data="{ caseIndex: 0 }">
        <div class="container split split--center">
            <div class="stack" data-reveal="left" style="--split-basis: 360px">
                <p class="eyebrow">{{ $site->t('recoating.result.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('recoating.result.title') }}</h2>
                <p class="body-text">{{ $site->t('recoating.result.text') }}</p>
                <div class="cluster" style="--cluster-gap: 8px">
                    @foreach ($cases as $i => $case)
                        <button type="button" class="btn btn--sm btn--mono" :class="{ 'is-active': caseIndex === {{ $i }} }" @click="caseIndex = {{ $i }}">{{ $case }}</button>
                    @endforeach
                </div>
            </div>
            <div class="split__wide" data-reveal style="--delay: 200ms; --split-basis: 620px">
                @foreach ($cases as $i => $case)
                    {{-- photos: «Настройки сайта» → recoat_case_N_before / _after; drawn rollers until then --}}
                    <x-site.compare x-show="caseIndex === {{ $i }}" :x-cloak="$i > 0"
                        :before="$site->image('recoat_case_'.($i + 1).'_before')" :after="$site->image('recoat_case_'.($i + 1).'_after')"
                        :art-tone="['conveyor', 'printing', 'press'][$i] ?? 'conveyor'"
                        :before-caption="$site->t('recoating.result.before', ['case' => $case])" :after-caption="$site->t('recoating.result.after', ['case' => $case])" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Shipping from the regions --}}
    <section class="section section--tint section--line-top section--line-bottom" x-reveal>
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__main">
                    <p class="eyebrow">{{ $site->t('recoating.shipping.eyebrow') }}</p>
                    <h2 class="h2">{{ $site->t('recoating.shipping.title') }}</h2>
                </div>
            </div>
            @php($icons = ['M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z M12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', 'M1 3h15v13H1z M16 8h4l3 3v5h-7z M5.5 21a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z M18.5 21a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z', 'M20 6L9 17l-5-5'])
            <div class="grid grid--fit" style="--grid-min: 280px">
                @foreach ($site->t('recoating.shipping.items') as $item)
                    <div class="panel panel--pad stack stack--s" data-reveal style="--delay: {{ 120 + $loop->index * 140 }}ms">
                        <span class="icon-tile"><x-site.icon :path="$icons[$loop->index]" /></span>
                        <span class="meta">{{ $site->t('recoating.shipping.step', ['n' => $loop->iteration]) }}</span>
                        <span class="h3">{{ $item['title'] }}</span>
                        <span class="body-text">{{ $item['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Request --}}
    <section id="request" class="section section--accent section--line-bottom">
        <div class="container split">
            <div class="stack" style="--split-basis: 360px">
                <p class="eyebrow">{{ $site->t('recoating.request.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('recoating.request.title') }}</h2>
                <p class="lead text-ink">{{ $site->t('recoating.request.text') }}</p>
            </div>
            <x-site.lead-form type="recoating" style="--split-basis: 520px">
                <input type="hidden" name="items" :value="itemsJson">
                <x-site.field name="name" :label="$site->t('recoating.request.name')" :placeholder="$site->t('common.form.name_placeholder')" autocomplete="name" />
                <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
                <label class="field"><span>{{ $site->t('recoating.request.diameter') }}</span><input type="text" inputmode="decimal" class="input" placeholder="Ø" x-model="sizes.D"></label>
                <label class="field"><span>{{ $site->t('recoating.request.length') }}</span><input type="text" inputmode="decimal" class="input" placeholder="L" x-model="sizes.L"></label>
                <label class="field"><span>{{ $site->t('recoating.request.quantity') }}</span><input type="text" inputmode="numeric" class="input" placeholder="{{ $site->t('common.pcs') }}" x-model="sizes.qty"></label>
                <div class="field"><span>{{ $site->t('recoating.request.material') }}</span><span class="input recoating-material" x-text="materialName"></span></div>
                <x-site.dropzone :label="$site->t('recoating.request.photo')" :hint="$site->t('common.form.drop_photo')" accept="image/*" />
                <x-site.submit :label="$site->t('common.form.send_application')" />
            </x-site.lead-form>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container split">
            <h2 class="h2" style="--split-basis: 300px">{{ $site->t('recoating.faq.title') }}</h2>
            <x-site.faq class="split__wide" style="--split-basis: 560px" :items="$site->t('recoating.faq.items')" />
        </div>
    </section>
</div>
</x-site.layout>
