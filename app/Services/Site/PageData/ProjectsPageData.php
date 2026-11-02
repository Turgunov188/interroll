<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Industry;
use App\Models\Project;
use App\Services\Site\ContentCache;
use App\Services\Site\ViewModels\Option;
use App\Services\Site\ViewModels\ProjectCase;

/** «Работы»: published projects and the industry filter (only industries that have projects). */
final class ProjectsPageData implements PageData
{
    public const KPIS = 3;

    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('projects', $locale, function () use ($locale): array {
            $projects = Project::query()->published()->with('industry')->ordered()->get();

            return [
                'industries' => $projects->pluck('industry')->filter()->unique('id')->sortBy('sort')->values()
                    ->map(fn (Industry $i) => new Option($i->slug, (string) $i->tr('name', $locale)))->all(),
                'projects' => $projects->map(fn (Project $p) => $this->project($p, $locale))->all(),
            ];
        });
    }

    private function project(Project $project, Locale $locale): ProjectCase
    {
        return new ProjectCase(
            slug: (string) $project->slug,
            industry: $project->industry?->slug ?? '',
            industryName: (string) $project->industry?->tr('name', $locale),
            client: (string) $project->tr('client', $locale),
            title: (string) $project->tr('title', $locale),
            summary: (string) $project->tr('short', $locale),
            cover: $project->fileUrl('cover'),
            before: $project->fileUrl('photo_before'),
            after: $project->fileUrl('photo_after'),
            kpis: collect($project->kpis ?? [])
                ->map(fn (array $kpi): array => [
                    'value' => trim((string) ($kpi['value'] ?? '')),
                    'label' => $this->text($kpi['label'] ?? null, $locale),
                ])
                ->filter(fn (array $kpi): bool => $kpi['value'] !== '')
                ->take(self::KPIS)
                ->values()
                ->all(),
            problem: (string) $project->tr('problem', $locale),
            solution: (string) $project->tr('solution', $locale),
            result: (string) $project->tr('result', $locale),
            quote: (string) $project->tr('quote', $locale),
            quoteAuthor: (string) $project->quote_author,
        );
    }

    /** @param  array<string, string>|string|null  $value */
    private function text(array|string|null $value, Locale $locale): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        return (string) (($value[$locale->value] ?? null) ?: ($value[Locale::main()->value] ?? ''));
    }
}
