{{-- Product page. Data: App\Services\Site\PageData\ProductPageData ($product: ProductPage, $related). --}}
@php
    $views = array_values(array_filter([
        ($product->isConfigurable() || $product->drawings) ? 'draw' : null,
        ($product->isConfigurable() || $product->model) ? '3d' : null,
        ($product->photos || (! $product->isConfigurable() && ! $product->drawings && ! $product->model)) ? 'photo' : null,
    ]));
    $tabs = array_values(array_filter([
        'spec',
        $product->applications ? 'use' : null,
        $product->documents ? 'docs' : null,
    ]));
    $materials = $product->material === 'rz' ? ['nbr', 'epdm', 'pu'] : ['pu', 'nbr', 'epdm'];
    $config = [
        'code' => $product->code,
        'name' => $product->name,
        'type' => $product->configurator,
        'parameters' => $product->parameters,
        'material' => $materials[0],
        'hardness' => $product->hardness,
        'view' => $views[0],
        'tab' => 'spec',
        'hasModel' => (bool) $product->model,
        'texts' => [
            'short' => ['pu' => 'PU', 'nbr' => 'NBR', 'epdm' => 'EPDM'],
            'long' => $site->t('product.materials_long'),
            'hints' => $site->t('product.hardness_hints'),
            'coating' => $site->t('product.coating'),
            'stamp' => mb_strtoupper($site->t('product.stamp', ['code' => $product->code, 'name' => $product->name])),
            'drawing' => $site->t('product.drawing_label'),
            'pcs' => $site->t('common.pcs'),
        ],
    ];
@endphp
<x-site.layout :title="$product->name" :description="$product->description" cta="#request" :image="$product->photos[0]->url ?? null">
<div x-data="productPage(@js($config))">
    <section class="section product-top">
        <div class="container stack stack--l">
            <x-site.breadcrumbs class="rise" :items="[
                $site->t('common.home') => $site->url('index'),
                $site->t('common.catalog') => $site->url('catalog'),
                $product->category => null,
                $product->code => null,
            ]" />

            <div class="product-layout">
                {{-- Drawing / 3D / photos --}}
                <div class="panel product-viewer rise" style="--delay: 80ms">
                    <div class="product-viewer__bar">
                        <div class="segmented segmented--accent product-viewer__tabs" role="tablist" aria-label="{{ $site->t('product.views_label') }}">
                            @foreach ($views as $view)
                                <button type="button" role="tab" class="segmented__item" :aria-selected="view === @js($view)" @click="view = @js($view)">{{ $site->t("product.views.{$view}") }}</button>
                            @endforeach
                        </div>
                        @if ($product->isConfigurable())
                            <span class="meta product-viewer__scale" x-show="view === 'draw'" x-text="'M 1:' + scale"></span>
                        @endif
                    </div>

                    @if ($product->isConfigurable())
                        <div class="blueprint product-viewer__drawing" x-show="view === 'draw'" x-html="drawing"></div>
                    @else
                        <div class="product-viewer__files" x-show="view === 'draw'">
                            @foreach ($product->drawings as $drawing)
                                <a href="{{ $drawing->url }}" target="_blank" rel="noopener" class="media product-viewer__file">
                                    @if ($drawing->isImage)<img src="{{ $drawing->url }}" alt="{{ $drawing->title }}" loading="lazy">@endif
                                    <span class="chip">{{ $drawing->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div x-show="view === '3d'" x-cloak>
                        @if ($product->model)
                            <div class="product-viewer__model" x-init="$watch('view', (v) => v === '3d' && loadModelViewer())">
                                <model-viewer src="{{ $product->model->url }}" alt="{{ $product->model->title }}" camera-controls auto-rotate shadow-intensity="0.6" exposure="1"></model-viewer>
                                <span class="stage3d__hint">{{ $site->t('common.rotate_hint') }}</span>
                            </div>
                        @else
                            <div class="stage3d" @mousemove="tilt($event)" @mouseleave="tilt(null)">
                                <div class="stage3d__origin">
                                    <div class="stage3d__tilt" :style="tiltStyle" x-ref="tilt">
                                        <div x-ref="model" :class="motion === 'roll' ? 'spin-x' : 'spin-y'" :style="modelStyle"></div>
                                    </div>
                                </div>
                                <span class="stage3d__hint">{{ $site->t('common.rotate_hint') }}</span>
                            </div>
                        @endif
                    </div>

                    <div x-show="view === 'photo'" @if ($views[0] !== 'photo') x-cloak @endif>
                        @if ($product->photos)
                            <div class="product-viewer__photos">
                                @foreach ($product->photos as $photo)
                                    <img src="{{ $photo->url }}" alt="{{ $photo->title }}" loading="lazy" decoding="async">
                                @endforeach
                            </div>
                        @else
                            <x-site.media class="product-viewer__placeholder"><span>{{ $site->t('product.photo_placeholder') }}</span></x-site.media>
                        @endif
                    </div>

                    @if ($product->isConfigurable())
                        <dl class="product-viewer__chips mono">
                            <template x-for="chip in chips" :key="chip[0]">
                                <div><dt x-text="chip[0]"></dt><dd x-text="chip[1]"></dd></div>
                            </template>
                        </dl>
                    @endif
                </div>

                {{-- Name, configurator, quantity --}}
                <div class="product-info stack stack--m">
                    <div class="stack stack--s rise" style="--delay: 160ms">
                        <span class="chip product-info__code">{{ $product->code }} · {{ mb_strtoupper($product->category) }}</span>
                        <x-site.title tag="h1" class="h1 product-info__name" :lines="array_filter([$product->nameLead])" :accent="$product->nameAccent" />
                        <p class="lead">{{ $product->description }}</p>
                    </div>

                    <div class="panel rise" style="--delay: 240ms">
                        @if ($product->isConfigurable())
                            <div class="panel__head"><span>{{ $site->t('product.configurator') }}</span><span class="text-muted">{{ $site->t('product.sizes_in_mm') }}</span></div>

                            <div class="product-option stack stack--s">
                                <span class="label">{{ $site->t('product.material') }}</span>
                                <div class="segmented segmented--grid segmented--mono" style="--cols: 3">
                                    @foreach ($materials as $material)
                                        <button type="button" class="segmented__item" :aria-pressed="material === @js($material)" @click="material = @js($material)">{{ $site->t("common.materials.{$material}") }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="product-option stack stack--s">
                                <div class="cluster cluster--between"><span class="label">{{ $site->t('product.hardness') }}</span><b class="product-option__value" x-text="shore + ' A'"></b></div>
                                <div class="segmented segmented--grid segmented--mono" style="--cols: 5">
                                    @foreach ([70, 80, 85, 90, 95] as $value)
                                        <button type="button" class="segmented__item" :aria-pressed="shore === {{ $value }}" @click="shore = {{ $value }}">{{ $value }}A</button>
                                    @endforeach
                                </div>
                                <div class="hardness-scale" aria-hidden="true"><span :style="{ left: hardnessPosition + '%' }"></span></div>
                                <div class="cluster cluster--between meta product-option__hint"><span>{{ $site->t('product.soft') }}</span><span x-text="hardnessHint"></span><span>{{ $site->t('product.hard') }}</span></div>
                            </div>

                            <div class="product-option stack stack--m">
                                <template x-for="param in parameters" :key="param.key">
                                    <label class="stack stack--xs">
                                        <span class="cluster cluster--between"><span class="label label--case" x-text="param.label"></span><b x-text="values[param.key] + ' ' + param.unit"></b></span>
                                        <input type="range" class="range" :min="param.min" :max="limit(param)" :step="param.step" :value="values[param.key]" @input="set(param.key, $event.target.value)">
                                    </label>
                                </template>
                            </div>
                        @endif

                        <div class="product-option cluster cluster--between">
                            <div class="stepper">
                                <button type="button" class="stepper__btn" aria-label="{{ $site->t('product.quantity_less') }}" @click="qty = Math.max(1, qty - 1)">−</button>
                                <span class="stepper__value"><span x-text="qty">4</span>&nbsp;{{ $site->t('common.pcs') }}</span>
                                <button type="button" class="stepper__btn" aria-label="{{ $site->t('product.quantity_more') }}" @click="qty++">+</button>
                            </div>
                            <span class="card__more">{{ $site->t('common.price_on_request') }}</span>
                        </div>
                        <div class="product-option product-option--last">
                            <a href="#request" class="btn btn--dark btn--lg btn--block">{{ $product->isConfigurable() ? $site->t('product.send_with_size') : $site->t('product.send') }}</a>
                        </div>
                    </div>

                    @if ($product->documents)
                        <div class="cluster rise" style="--delay: 320ms">
                            <a href="{{ $product->documents[0]->url }}" target="_blank" rel="noopener" class="btn btn--mono">
                                <x-site.icon path="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z M14 3v6h6 M12 12v6 M9 15l3 3 3-3" :size="18" />
                                {{ $site->t('product.passport') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Specifications / applications / documents --}}
    <section class="section section--tight section--tint section--line-top section--line-bottom">
        <div class="container stack stack--l">
            <div class="segmented product-tabs" role="tablist">
                @foreach ($tabs as $tab)
                    <button type="button" role="tab" class="segmented__item" :aria-selected="tab === @js($tab)" @click="tab = @js($tab)">{{ $site->t("product.tabs.{$tab}") }}</button>
                @endforeach
            </div>

            <div class="split" x-show="tab === 'spec'">
                <table class="data-table split__wide" style="--split-basis: 520px">
                    <tbody>
                        @if ($product->isConfigurable())
                            <tr><th>{{ $site->t('product.spec.material') }}</th><td x-text="materialName"></td></tr>
                            <tr><th>{{ $site->t('product.spec.hardness') }}</th><td x-text="shore + ' Shore A'"></td></tr>
                            <template x-for="param in parameters" :key="param.key">
                                <tr><th x-text="param.label"></th><td x-text="values[param.key] + ' ' + param.unit"></td></tr>
                            </template>
                        @else
                            @if ($product->machine)<tr><th>{{ $site->t('product.spec.machine') }}</th><td>{{ $product->machine }}</td></tr>@endif
                            @if ($product->material !== 'ot')<tr><th>{{ $site->t('product.spec.material') }}</th><td>{{ $product->materialLabel }}</td></tr>@endif
                            @if ($product->hardnessLabel)<tr><th>{{ $site->t('product.spec.hardness') }}</th><td>{{ $product->hardnessLabel }}</td></tr>@endif
                        @endif
                        @if ($product->castElastomer)<tr><th>{{ $site->t('product.spec.temperature') }}</th><td>{{ $site->t('product.spec.temperature_value') }}</td></tr>@endif
                        <tr><th>{{ $site->t('product.spec.lead_time') }}</th><td>{{ $site->t('product.spec.lead_time_value') }}</td></tr>
                        @foreach ($product->specs as $spec)
                            <tr><th>{{ $spec['name'] }}</th><td>{{ $spec['value'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($product->castElastomer)
                    <div class="stack" style="--split-basis: 340px">
                        <p class="h3">{{ $site->t('product.why.title') }}</p>
                        @foreach ($site->t('product.why.items') as $item)
                            <p class="bar-note">{{ $item }}</p>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($product->applications)
                <div class="grid grid--fit" style="--grid-min: 240px" x-show="tab === 'use'" x-cloak>
                    @foreach ($product->applications as $application)
                        <div class="panel panel--pad stack stack--s">
                            <span class="meta">{{ sprintf('/%02d', $loop->iteration) }}</span>
                            <span class="card__title">{{ $application['name'] }}</span>
                            @if ($application['hardness'])<span class="meta">{{ $site->t('product.recommendation', ['hardness' => $application['hardness']]) }}</span>@endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($product->documents)
                <ul class="panel product-docs" x-show="tab === 'docs'" x-cloak>
                    @foreach ($product->documents as $document)
                        <li><a href="{{ $document->url }}" target="_blank" rel="noopener"><span>{{ $document->title }}</span><span class="mono text-small">{{ $document->meta }} ↓</span></a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- Related products --}}
    @if ($related)
        <section class="section section--tight">
            <div class="container">
                <div class="section-head">
                    <h2 class="h2">{{ $site->t('product.related.title') }}</h2>
                    <a href="{{ $site->url('catalog') }}" class="mono text-small link-underline">{{ mb_strtoupper($site->t('product.related.all')) }}</a>
                </div>
                <div class="grid grid--fit" style="--grid-min: 260px">
                    @foreach ($related as $item)
                        <a href="{{ $item->url }}" class="card">
                            <span class="media media--center related-card__icon"><x-site.icon :path="$item->icon" :size="80" :box="64" /></span>
                            <span class="card__body">
                                <span class="meta">{{ $item->code }}</span>
                                <span class="card__title">{{ $item->name }}</span>
                                <span class="card__more">{{ $site->t('common.more') }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Request with the chosen configuration --}}
    <section id="request" class="section section--accent section--line-top">
        <div class="container split">
            <div class="stack" style="--split-basis: 360px">
                <p class="eyebrow">{{ $site->t('product.request.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('product.request.title') }}</h2>
                <p class="panel panel--pad mono product-summary" x-text="summary"></p>
            </div>
            <x-site.lead-form type="quote" style="--split-basis: 480px">
                <input type="hidden" name="items" :value="itemsJson">
                <x-site.field name="name" :label="$site->t('common.form.company_name')" :placeholder="$site->t('common.form.name_placeholder')" autocomplete="name" />
                <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
                <x-site.field name="message" textarea rows="3" full :label="$site->t('common.form.comment')" :placeholder="$site->t('product.request.comment_placeholder')" />
                <x-site.dropzone :label="$site->t('common.form.file_optional')" />
                <x-site.submit :label="$site->t('common.form.send_request')" />
            </x-site.lead-form>
        </div>
    </section>
</div>
</x-site.layout>
