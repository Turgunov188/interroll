{{-- Part finder: 4 questions → material and hardness. Data: PartFinderPageData ($suggestions by part type). --}}
@php
    $types = $site->t('finder.type.options');
    $industries = $site->t('finder.industry.options');
    $icons = [
        'roller' => 'M10 22h44v20H10z M10 32h44 M4 30h6v4H4z M54 30h6v4h-6z',
        'wheel' => 'M32 6a26 26 0 1 0 0.01 0z M32 18a14 14 0 1 0 0.01 0z M32 28a4 4 0 1 0 0.01 0z',
        'plate' => 'M6 42l40-22 12 8-40 22z M14 40l34-18',
        'coupling' => 'M32 6l5 13 13-5-5 13 13 5-13 5 5 13-13-5-5 13-5-13-13 5 5-13-13-5 13-5-5-13 13 5z',
        'unknown' => 'M32 54a22 22 0 1 0 0-44 22 22 0 0 0 0 44z M26 26a6 6 0 1 1 8 5.6c-1.4.6-2 1.6-2 3.4 M32 42v.5',
    ];
    $config = [
        'types' => collect($types)->map(fn ($o) => $o['title']),
        'industries' => collect($industries)->map(fn ($o) => $o['title']),
        'loads' => collect($site->t('finder.conditions.loads'))->map(fn ($o) => $o['title']),
        'abrasions' => collect($site->t('finder.conditions.abrasions'))->map(fn ($o) => $o['title']),
        'environments' => collect($site->t('finder.environment.options'))->map(fn ($o) => $o['short']),
        'materials' => collect(config('site.materials'))->map(fn ($m, $key) => ['name' => $site->t("common.materials.{$key}"), 'levels' => $m['levels']]),
        'notes' => $site->t('finder.notes'),
        'reasons' => $site->t('finder.reasons'),
        'levels' => $site->t('common.levels'),
        'abrasionWord' => $site->t('finder.aside.abrasion'),
        'suggestions' => $suggestions,
    ];
@endphp
<x-site.layout :title="$site->t('meta.part-finder.title')" :description="$site->t('meta.part-finder.description')">
<section class="section finder" x-data="partFinder(@js($config))">
    <div class="container stack stack--l">
        <div class="cluster cluster--between cluster--end" style="--cluster-gap: 20px">
            <div class="stack">
                <p class="eyebrow">{{ $site->t('finder.eyebrow') }}</p>
                <x-site.title tag="h1" class="h1" :lines="[$site->t('finder.title')]" :accent="$site->t('finder.accent')" />
                <p class="lead">{{ $site->t('finder.lead') }}</p>
            </div>
            <span class="eyebrow" x-text="@js($site->t('finder.step')).replace(':n', '0' + step)">{{ $site->t('finder.step', ['n' => '01']) }}</span>
        </div>

        <ol class="finder-progress">
            @foreach ($site->t('finder.progress') as $i => $label)
                <li>
                    <button type="button" class="finder-progress__step" :class="progressClass({{ $i + 1 }})" :disabled="!canOpen({{ $i + 1 }})" @click="go({{ $i + 1 }})">
                        <span class="mono">{{ sprintf('%02d', $i + 1) }}</span><span>{{ $label }}</span>
                    </button>
                </li>
            @endforeach
        </ol>

        <div class="finder-layout">
            <div class="panel panel--pad finder-panel">
                {{-- 1. Part type --}}
                <div class="stack stack--m finder-step" x-show="step === 1">
                    <h2 class="h3">{{ $site->t('finder.type.title') }}</h2>
                    <div class="grid grid--fit" style="--grid-min: 180px; --grid-gap: 12px">
                        @foreach ($types as $key => $option)
                            <button type="button" class="finder-option" :aria-pressed="type === @js($key)" @click="choose('type', @js($key))">
                                <x-site.icon :path="$icons[$key]" :size="56" :box="64" :stroke="2.2" />
                                <span class="finder-option__title">{{ $option['title'] }}</span>
                                <span class="finder-option__text">{{ $option['text'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Industry --}}
                <div class="stack stack--m finder-step" x-show="step === 2" x-cloak>
                    <h2 class="h3">{{ $site->t('finder.industry.title') }}</h2>
                    <div class="grid grid--fit" style="--grid-min: 200px; --grid-gap: 12px">
                        @foreach ($industries as $key => $option)
                            <button type="button" class="finder-option" :aria-pressed="industry === @js($key)" @click="choose('industry', @js($key))">
                                <span class="finder-option__title">{{ $option['title'] }}</span>
                                <span class="finder-option__text">{{ $option['text'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Load and wear --}}
                <div class="stack stack--l finder-step" x-show="step === 3" x-cloak>
                    <h2 class="h3">{{ $site->t('finder.conditions.title') }}</h2>
                    @foreach (['load' => 'loads', 'abrasion' => 'abrasions'] as $field => $list)
                        <div class="stack stack--s">
                            <span class="label">{{ $site->t("finder.conditions.{$field}") }}</span>
                            <div class="segmented segmented--grid finder-scale" style="--cols: 3">
                                @foreach ($site->t("finder.conditions.{$list}") as $key => $option)
                                    <button type="button" class="segmented__item" :aria-pressed="{{ $field }} === @js($key)" @click="{{ $field }} = @js($key)">
                                        <b>{{ $option['title'] }}</b><span>{{ $option['text'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 4. Environment --}}
                <div class="stack stack--l finder-step" x-show="step === 4" x-cloak>
                    <h2 class="h3">{{ $site->t('finder.environment.title') }}</h2>
                    <label class="stack stack--s">
                        <span class="cluster cluster--between"><span class="label">{{ $site->t('finder.environment.temperature') }}</span><b class="finder-temp" x-text="temperature + ' °C'"></b></span>
                        <input type="range" class="range" min="-40" max="200" step="5" x-model.number="temperature">
                        <span class="cluster cluster--between meta"><span>−40</span><span>0</span><span>+80</span><span>+200</span></span>
                    </label>
                    <div class="grid grid--fit" style="--grid-min: 220px; --grid-gap: 12px">
                        @foreach ($site->t('finder.environment.options') as $key => $option)
                            <button type="button" class="finder-option" :aria-pressed="{{ $key }}" @click="{{ $key }} = !{{ $key }}">
                                <span class="cluster cluster--between finder-option__head"><b>{{ $option['title'] }}</b><span class="chip" :class="{ 'chip--dark': {{ $key }} }" x-text="{{ $key }} ? @js($site->t('common.yes')) : @js($site->t('common.no'))"></span></span>
                                <span class="finder-option__text">{{ $option['text'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Result --}}
                <div class="stack stack--m finder-step pop" x-show="step === 5" x-cloak>
                    <span class="label">{{ $site->t('finder.result.label') }}</span>
                    <div class="cluster finder-result" style="--cluster-gap: 16px">
                        <div class="finder-result__main stack stack--s"><span class="label text-ink">{{ $site->t('finder.result.material') }}</span><b class="finder-result__value" x-text="materialName"></b><span x-text="note"></span></div>
                        <div class="panel panel--pad stack stack--s finder-result__side"><span class="label">{{ $site->t('finder.result.hardness') }}</span><b class="finder-result__value" x-text="result.hardness"></b><span class="text-soft">Shore A</span></div>
                    </div>
                    <div class="stack stack--s">
                        <span class="label">{{ $site->t('finder.result.why') }}</span>
                        <ul class="check-list"><template x-for="reason in reasons" :key="reason"><li class="check-list__item" x-text="reason"></li></template></ul>
                    </div>
                    <div class="stack stack--s" x-show="products.length">
                        <span class="label">{{ $site->t('finder.result.products') }}</span>
                        <div class="grid grid--fit" style="--grid-min: 200px; --grid-gap: 12px">
                            <template x-for="product in products" :key="product.code">
                                <a :href="product.url" class="finder-option"><span class="meta" x-text="product.code"></span><b x-text="product.name"></b><span class="card__more">{{ $site->t('common.more') }}</span></a>
                            </template>
                        </div>
                    </div>
                    <p class="meta finder-disclaimer">{{ $site->t('finder.result.disclaimer') }}</p>

                    <x-site.lead-form type="wizard" form-class="lead-form lead-form--flat finder-form">
                        <input type="hidden" name="details[part]" :value="answers.part">
                        <input type="hidden" name="details[industry]" :value="answers.industry">
                        <input type="hidden" name="details[conditions]" :value="answers.load">
                        <input type="hidden" name="details[environment]" :value="answers.environment">
                        <input type="hidden" name="details[recommendation]" :value="summary">
                        <p class="field--full finder-form__title">{{ $site->t('finder.result.form_title') }}</p>
                        <x-site.field name="name" :label="$site->t('common.form.company_name')" :placeholder="$site->t('common.form.name_placeholder')" autocomplete="name" />
                        <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
                        <x-site.submit :label="$site->t('finder.result.request')" />
                    </x-site.lead-form>
                    <button type="button" class="btn finder-restart" @click="restart()">{{ $site->t('finder.result.restart') }}</button>
                </div>

                <div class="finder-nav" x-show="step < 5">
                    <button type="button" class="btn" :style="{ visibility: step > 1 ? 'visible' : 'hidden' }" @click="go(step - 1)">{{ $site->t('finder.back') }}</button>
                    <button type="button" class="btn btn--dark" :disabled="!canNext" @click="go(step + 1)">
                        <span x-text="step === 4 ? @js($site->t('finder.show')) : @js($site->t('finder.next'))">{{ $site->t('finder.next') }}</span> →
                    </button>
                </div>
            </div>

            <aside class="finder-aside stack stack--s" aria-label="{{ $site->t('finder.aside_label') }}">
                <div class="panel panel--dark panel--pad stack stack--s">
                    <span class="eyebrow text-accent">{{ $site->t('finder.aside.live') }}</span>
                    <b class="h3" x-text="materialName"></b>
                    <span class="mono text-small" x-text="result.hardness + ' Shore A'"></span>
                    @foreach ($site->t('common.properties') as $property)
                        <span class="meter" style="--meter-color: var(--c-accent)">
                            <span class="meter__head"><span>{{ $property }}</span><span x-text="levelName({{ $loop->index }})"></span></span>
                            <span class="meter__track"><span class="meter__fill" :style="{ '--level': level({{ $loop->index }}) }"></span></span>
                        </span>
                    @endforeach
                </div>
                <div class="panel panel--pad stack stack--s">
                    <span class="label">{{ $site->t('finder.aside.answers') }}</span>
                    @foreach (['part', 'industry', 'load', 'environment'] as $answer)
                        <span class="finder-answer"><span class="text-muted">{{ $site->t("finder.aside.{$answer}") }}</span><b x-text="answers.{{ $answer }}"></b></span>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
</section>
</x-site.layout>
