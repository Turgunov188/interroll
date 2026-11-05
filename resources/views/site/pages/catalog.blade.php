{{-- Catalog. Data: App\Services\Site\PageData\CatalogPageData ($products, $categories, $types, $machines, $industries, $silicone). --}}
@php
    $all = new \App\Services\Site\ViewModels\Option('all', $site->t('common.all'));
    $tabs = array_merge([$all], $categories);
    $script = [
        'products' => array_map(fn ($p) => $p->forScript(), $products),
        'types' => array_map(fn ($options) => array_map(fn ($o) => ['value' => $o->value], $options), $types),
    ];
@endphp
<x-site.layout :title="$site->t('meta.catalog.title')" :description="$site->t('meta.catalog.description')" cta="#request">
<div x-data="catalog(@js($script))">

    <section class="section--hero catalog-hero">
        <div class="container stack stack--l">
            <x-site.breadcrumbs class="rise" :items="[$site->t('common.home') => $site->url('index'), $site->t('catalog.breadcrumb') => null]" />
            <div class="cluster cluster--between cluster--end" style="--cluster-gap: 24px">
                <x-site.title tag="h1" class="display rise" style="--delay: 80ms" :lines="[$site->t('catalog.title')]" :accent="$site->t('catalog.accent')" />
                <dl class="catalog-stats mono rise" style="--delay: 200ms">
                    <div><dt>{{ $site->t('catalog.stats.items') }}</dt><dd>{{ count($products) }}</dd></div>
                    <div><dt>{{ $site->t('catalog.stats.sections') }}</dt><dd>{{ count($categories) }}</dd></div>
                    <div><dt>{{ $site->t('catalog.stats.days') }}</dt><dd>{{ $site->setting('lead_days') }}</dd></div>
                </dl>
            </div>
            <label class="search rise" style="--delay: 300ms">
                <span class="label catalog-search__label">{{ $site->t('catalog.search.label') }}</span>
                <x-site.icon class="search__icon" path="M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z M20 20l-4-4" :stroke="2.2" />
                <input type="search" class="input input--search" x-model.debounce.150ms="query" placeholder="{{ $site->t('catalog.search.placeholder') }}">
            </label>
        </div>
    </section>

    <nav class="catalog-tabs" aria-label="{{ $site->t('catalog.tabs_label') }}">
        <div class="container">
            <div class="segmented segmented--scroll segmented--accent catalog-tabs__list">
                @foreach ($tabs as $tab)
                    <button type="button" class="segmented__item" :aria-pressed="category === @js($tab->value)" @click="category = @js($tab->value)">
                        {{ $tab->label }} <span class="segmented__count" x-text="countIn(@js($tab->value))"></span>
                    </button>
                @endforeach
            </div>
            @foreach ($types as $section => $options)
                <div class="segmented segmented--scroll segmented--mono catalog-tabs__types" x-show="category === @js($section)" x-cloak aria-label="{{ $site->t('catalog.filters.type') }}">
                    @foreach (array_merge([$all], $options) as $option)
                        <button type="button" class="segmented__item" :aria-pressed="type === @js($option->value)" @click="type = @js($option->value)">
                            {{ $option->label }} <span class="segmented__count" x-text="countType(@js($option->value))"></span>
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </nav>

    <section class="section catalog-body">
        <div class="container catalog-layout">
            <aside class="panel panel--pad catalog-filters stack stack--m" aria-label="{{ $site->t('catalog.filters.label') }}" :class="{ 'is-open': filtersOpen }">
                <button type="button" class="btn btn--block btn--mono catalog-filters__toggle" :aria-expanded="filtersOpen" @click="filtersOpen = !filtersOpen">
                    {{ $site->t('catalog.filters.title') }} <span aria-hidden="true" x-text="filtersOpen ? '−' : '+'">+</span>
                </button>
                <div class="cluster cluster--between mono text-small">
                    <span class="eyebrow">{{ $site->t('catalog.filters.title') }}</span>
                    <button type="button" class="catalog-filters__reset" @click="reset()">{{ $site->t('catalog.filters.reset') }}</button>
                </div>

                {{-- Sections and their part types as a tree (desktop; phones use the tab rows above) --}}
                <nav class="catalog-tree" aria-label="{{ $site->t('catalog.tabs_label') }}">
                    <p class="label catalog-filters__legend">{{ $site->t('catalog.filters.sections') }}</p>
                    <ul class="catalog-tree__list" role="list">
                        @foreach ($tabs as $tab)
                            <li>
                                <button type="button" class="catalog-tree__item" :aria-pressed="category === @js($tab->value)" :class="{ 'is-empty': !countIn(@js($tab->value)) }" @click="category = @js($tab->value)">
                                    <span>{{ $tab->label }}</span><span class="catalog-tree__count" x-text="countIn(@js($tab->value))"></span>
                                </button>
                                @if (! empty($types[$tab->value]))
                                    <ul class="catalog-tree__list catalog-tree__types" role="list" x-show="category === @js($tab->value)" x-cloak>
                                        @foreach (array_merge([$all], $types[$tab->value]) as $option)
                                            <li>
                                                <button type="button" class="catalog-tree__item catalog-tree__item--type" :aria-pressed="type === @js($option->value)" :class="{ 'is-empty': !countType(@js($option->value)) }" @click="type = @js($option->value)">
                                                    <span>{{ $option->label }}</span><span class="catalog-tree__count" x-text="countType(@js($option->value))"></span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>

                @if ($machines)
                    <fieldset class="stack stack--xs" x-show="machines.length" x-cloak>
                        <legend class="label catalog-filters__legend">{{ $site->t('catalog.filters.machine') }}</legend>
                        <select class="input" x-model="machine" aria-label="{{ $site->t('catalog.filters.machine') }}">
                            <option value="all">{{ $site->t('catalog.filters.machine_all') }}</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine->value }}" x-show="machines.includes(@js($machine->value))">{{ $machine->label }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                @endif

                <fieldset class="stack stack--xs" x-show="hasElastomers">
                    <legend class="label catalog-filters__legend">{{ $site->t('catalog.filters.material') }}</legend>
                    <label class="checkbox"><input type="checkbox" x-model="materials.pu"> {{ $site->t('catalog.filters.polyurethane') }}</label>
                    <label class="checkbox"><input type="checkbox" x-model="materials.rz"> {{ $site->t('catalog.filters.rubber') }}</label>
                    @if ($silicone)<label class="checkbox"><input type="checkbox" x-model="materials.si"> {{ $site->t('catalog.filters.silicone') }}</label>@endif
                </fieldset>

                <fieldset class="stack stack--s" x-show="hasHardness">
                    <legend class="label catalog-filters__legend">{{ $site->t('catalog.filters.hardness') }}</legend>
                    <div class="segmented segmented--grid segmented--mono" style="--cols: 4">
                        @foreach ($site->t('catalog.filters.hardness_options') as $value => $label)
                            <button type="button" class="segmented__item" :aria-pressed="hardness === @js($value)" @click="hardness = @js($value)">{{ $label }}</button>
                        @endforeach
                    </div>
                </fieldset>

                @if ($industries)
                    <fieldset class="stack stack--xs">
                        <legend class="label catalog-filters__legend">{{ $site->t('catalog.filters.industry') }}</legend>
                        @foreach ($industries as $industry)
                            <label class="checkbox"><input type="checkbox" value="{{ $industry->value }}" x-model="industries"> {{ $industry->label }}</label>
                        @endforeach
                    </fieldset>
                @endif

                <div class="catalog-filters__help stack stack--s">
                    <p class="catalog-filters__help-title">{{ $site->t('catalog.filters.no_size') }}</p>
                    <a href="#request" class="btn btn--dark btn--mono">{{ $site->t('catalog.filters.send_drawing') }}</a>
                    <a href="{{ $site->url('part-finder') }}" class="btn btn--accent btn--mono">{{ $site->t('catalog.filters.finder') }}</a>
                </div>
            </aside>

            <div class="catalog-results stack">
                <div class="cluster cluster--between mono text-small catalog-results__bar">
                    <span>{{ $site->t('catalog.found') }} <b x-text="shown.length">{{ count($products) }}</b> {{ $site->t('common.pcs') }}</span>
                    <label class="cluster" style="--cluster-gap: 10px">
                        {{ $site->t('catalog.sort.label') }}
                        <select class="input" x-model="sort">
                            @foreach ($site->t('catalog.sort.options') as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="panel panel--pad catalog-empty stack" x-show="shown.length === 0" x-cloak>
                    <p class="h3">{{ $site->t('catalog.empty.title') }}</p>
                    <p class="body-text">{{ $site->t('catalog.empty.text') }}</p>
                    <a href="#request" class="btn btn--accent">{{ $site->t('catalog.empty.action') }}</a>
                </div>

                <div class="grid catalog-grid">
                    @foreach ($products as $product)
                        <article class="card card--hover catalog-card" x-show="isShown(@js($product->code))" :style="{ order: position(@js($product->code)) }">
                            <a href="{{ $product->url }}" class="media media--center media--contain catalog-card__photo" tabindex="-1">
                                <x-site.icon class="catalog-card__icon" :path="$product->icon" :size="96" :box="64" />
                                @if ($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
                                @else
                                    <span class="media__badge media__badge--bl">{{ $site->t('common.photo') }}</span>
                                @endif
                                <span class="chip media__badge media__badge--tr">{{ $product->code }}</span>
                            </a>
                            <div class="card__body">
                                <h2 class="card__title"><a href="{{ $product->url }}">{{ $product->name }}</a></h2>
                                <div class="chips">
                                    @if ($product->machineName)<span class="chip chip--accent">{{ $product->machineName }}</span>@endif
                                    @if ($product->material !== 'ot')<span class="chip">{{ $product->materialLabel }}</span>@endif
                                    @if ($product->hardness)<span class="chip">{{ $product->hardness }} Shore</span>@endif
                                </div>
                                <p class="card__text">{{ $product->summary }}</p>
                                <div class="card__foot">
                                    <span class="card__more">{{ $site->t('common.price_on_request') }}</span>
                                    <button type="button" class="btn btn--sm btn--accent" :class="{ 'is-active': inCart(@js($product->code)) }" @click="toggle(@js($product->code))"
                                            x-text="inCart(@js($product->code)) ? @js($site->t('catalog.added')) : @js($site->t('catalog.add'))">{{ $site->t('catalog.add') }}</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="request" class="section section--tight section--accent section--line-top">
        <div class="container cluster cluster--between" style="--cluster-gap: 32px">
            <div class="stack stack--s catalog-special">
                <p class="eyebrow">{{ $site->t('catalog.special.eyebrow') }}</p>
                <h2 class="h2">{{ $site->t('catalog.special.title') }}</h2>
            </div>
            <div class="cluster">
                <a href="{{ $site->url('index') }}#request" class="btn btn--dark btn--lg">{{ $site->t('catalog.special.action') }}</a>
                <a href="{{ $site->tel() }}" class="btn btn--lg btn--mono">{{ $site->setting('phone') }}</a>
            </div>
        </div>
    </section>

    {{-- Request cart: several products in one request --}}
    <button type="button" class="fab" x-show="cartSize > 0" x-transition x-cloak @click="openCart()">
        <span class="fab__count" x-text="cartSize"></span>{{ $site->t('catalog.cart.open') }}
    </button>

    <dialog class="drawer" x-ref="cart" aria-label="{{ $site->t('catalog.cart.title') }}" @close="cartOpen = false" @click.self="closeCart()">
        <div class="drawer__head">
            <span class="stack stack--xs">
                <span class="label">{{ $site->t('catalog.cart.title') }}</span>
                <b class="drawer__title" x-text="summary(@js($site->t('catalog.cart.summary')))"></b>
            </span>
            <button type="button" class="dialog__close" aria-label="{{ $site->t('catalog.cart.close') }}" @click="closeCart()">×</button>
        </div>

        <ul class="cart-list">
            <template x-for="line in cartLines" :key="line.code">
                <li class="cart-list__item">
                    <span class="media media--center cart-list__icon"><svg aria-hidden="true" width="36" height="36" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4"><path :d="line.icon"/></svg></span>
                    <span class="cart-list__name"><span class="meta" x-text="line.code"></span><b x-text="line.name"></b></span>
                    <span class="stepper stepper--s">
                        <button type="button" class="stepper__btn" aria-label="{{ $site->t('catalog.cart.less') }}" @click="change(line.code, -1)">−</button>
                        <span class="stepper__value" x-text="line.qty"></span>
                        <button type="button" class="stepper__btn" aria-label="{{ $site->t('catalog.cart.more') }}" @click="change(line.code, 1)">+</button>
                    </span>
                </li>
            </template>
        </ul>

        <x-site.lead-form type="quote" form-class="lead-form lead-form--column lead-form--flat" @lead:sent="clearCart()">
            <input type="hidden" name="items" :value="itemsJson">
            <x-site.field name="name" :label="$site->t('common.form.company_name')" :placeholder="$site->t('common.form.name_placeholder')" autocomplete="name" />
            <x-site.field name="phone" type="tel" :label="$site->t('common.form.phone')" :placeholder="$site->t('common.form.phone_placeholder')" autocomplete="tel" required />
            <x-site.submit :label="$site->t('catalog.cart.send')" />
            <p class="lead-form__note">{{ $site->t('catalog.cart.note') }}</p>

            <x-slot:success>
                <span class="success__title">{{ $site->t('catalog.cart.sent_title') }}</span>
                <span class="body-text">{{ $site->t('catalog.cart.sent_text') }}</span>
                <button type="button" class="btn btn--mono" @click="closeCart()">{{ $site->t('catalog.cart.back') }}</button>
            </x-slot:success>
        </x-site.lead-form>
    </dialog>
</div>
</x-site.layout>
