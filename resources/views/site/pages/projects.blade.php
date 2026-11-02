{{-- Finished jobs. Data: App\Services\Site\PageData\ProjectsPageData ($projects, $industries). --}}
@php($filters = array_merge([new \App\Services\Site\ViewModels\Option('all', $site->t('common.all'))], $industries))
<x-site.layout :title="$site->t('meta.projects.title')" :description="$site->t('meta.projects.description')">
<div x-data="filterList({ items: @js(array_map(fn ($p) => ['key' => $p->slug, 'group' => $p->industry], $projects)) })">
    <section class="section section--flush-bottom projects-head">
        <div class="container stack stack--m">
            <p class="eyebrow">{{ $site->t('projects.eyebrow') }}</p>
            <div class="cluster cluster--between cluster--end" style="--cluster-gap: 24px">
                <x-site.title tag="h1" class="h1" :lines="[$site->t('projects.title')]" :accent="$site->t('projects.accent')" />
                <p class="lead projects-head__lead">{{ $site->t('projects.lead') }}</p>
            </div>
            @if ($projects)
                <div class="segmented segmented--accent projects-filter" role="group" aria-label="{{ $site->t('projects.filters') }}">
                    @foreach ($filters as $filter)
                        <button type="button" class="segmented__item" :aria-pressed="group === @js($filter->value)" @click="group = @js($filter->value)">
                            {{ $filter->label }} <span class="segmented__count" x-text="count(@js($filter->value))"></span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if (! $projects)
                <p class="lead">{{ $site->t('projects.empty') }}</p>
            @endif
            <div class="grid" style="--grid-min: 360px; --grid-gap: 24px">
                @foreach ($projects as $project)
                    <button type="button" class="card card--hover project-card card-in" x-show="isShown(@js($project->slug))" :style="{ '--delay': delay(@js($project->slug)) }"
                            @click="$refs['case-{{ $project->slug }}'].showModal()">
                        <span class="project-card__photo">
                            <x-site.media class="project-card__media" :src="$project->cover" :caption="$project->title" :alt="$project->title" />
                            @if ($project->industryName)<span class="chip media__badge media__badge--tl">{{ $project->industryName }}</span>@endif
                        </span>
                        <span class="card__body">
                            <span class="card__title project-card__title">{{ $project->title }}</span>
                            <span class="card__text">{{ $project->summary }}</span>
                            @if ($project->kpis)
                                <span class="project-card__kpis">
                                    @foreach (array_slice($project->kpis, 0, 2) as $kpi)
                                        <span class="stack stack--xs"><b class="project-card__kpi">{{ $kpi['value'] }}</b><span class="label">{{ $kpi['label'] }}</span></span>
                                    @endforeach
                                </span>
                            @endif
                            <span class="card__more">{{ $site->t('projects.read') }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    @foreach ($projects as $project)
        <dialog class="dialog project-case" x-ref="case-{{ $project->slug }}" aria-label="{{ $project->title }}" @click.self="$el.close()">
            <div class="dialog__head">
                <span class="stack stack--xs">
                    <span class="label">{{ collect([$project->industryName, $project->client])->filter()->implode(' · ') }}</span>
                    <b class="h3">{{ $project->title }}</b>
                </span>
                <button type="button" class="dialog__close" aria-label="{{ $site->t('projects.close') }}" @click="$el.closest('dialog').close()">×</button>
            </div>
            <div class="project-case__photos">
                <x-site.media tone="steel" :src="$project->before" :caption="$site->t('projects.before')" />
                <x-site.media tone="warm" :src="$project->after" :caption="$site->t('projects.after')" />
            </div>
            <div class="project-case__blocks">
                @foreach (['problem' => $project->problem, 'solution' => $project->solution, 'result' => $project->result] as $key => $text)
                    <div class="project-case__block stack stack--s">
                        <span class="eyebrow project-case__step project-case__step--{{ $key }}"><span>{{ sprintf('%02d', $loop->iteration) }}</span>{{ $site->t("projects.blocks.{$key}") }}</span>
                        <span class="body-text text-ink">{{ $text }}</span>
                    </div>
                @endforeach
            </div>
            @if ($project->kpis)
                <div class="project-case__kpis">
                    @foreach ($project->kpis as $kpi)
                        <div><b>{{ $kpi['value'] }}</b><span class="label">{{ $kpi['label'] }}</span></div>
                    @endforeach
                </div>
            @endif
            <div class="project-case__foot">
                @if ($project->quote)
                    <blockquote class="project-case__quote">“{{ $project->quote }}”<span class="meta">{{ $project->quoteAuthor }}</span></blockquote>
                @endif
                <a href="{{ $site->url('contact') }}#request" class="btn btn--accent">{{ $site->t('projects.same') }}</a>
            </div>
        </dialog>
    @endforeach
</div>
</x-site.layout>
