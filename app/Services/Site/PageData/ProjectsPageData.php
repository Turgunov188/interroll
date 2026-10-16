<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Industry;
use App\Models\Project;
use App\Services\Site\ContentCache;

/**
 * «Работы» page: published projects and the industry filter (only industries that have projects).
 * Keys follow the design's case objects (build-tools/dynamic_patches.py → portfolio).
 */
final class ProjectsPageData implements PageData
{
    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('projects', $locale, function () use ($locale): array {
            $projects = Project::query()->published()->with('industry')->ordered()->get();

            $industries = $projects->pluck('industry')->filter()->unique('id')->sortBy('sort')->values();

            return [
                'industries' => $industries->map(fn (Industry $i): array => [$i->slug, $i->tr('name', $locale)])->all(),
                'projects' => $projects->map(fn (Project $p): array => $this->project($p, $locale))->all(),
            ];
        });
    }

    /** @return array<string, mixed> */
    private function project(Project $project, Locale $locale): array
    {
        $kpis = collect($project->kpis ?? [])
            ->map(fn (array $kpi): array => [
                'v' => trim((string) ($kpi['value'] ?? '')),
                'l' => $this->text($kpi['label'] ?? null, $locale),
            ])
            ->filter(fn (array $kpi): bool => $kpi['v'] !== '')
            ->take(3)
            ->values();

        return [
            'f' => $project->industry?->slug ?? '',
            'ind' => (string) $project->industry?->tr('name', $locale),
            'client' => (string) $project->tr('client', $locale),
            't' => (string) $project->tr('title', $locale),
            'short' => (string) $project->tr('short', $locale),
            'photo' => (string) $project->tr('title', $locale),
            'img' => $project->fileUrl('cover'),
            'before' => $project->fileUrl('photo_before'),
            'after' => $project->fileUrl('photo_after'),
            'k1' => $kpis[0]['v'] ?? '', 'k1l' => $kpis[0]['l'] ?? '',
            'k2' => $kpis[1]['v'] ?? '', 'k2l' => $kpis[1]['l'] ?? '',
            'kpis' => $kpis->all(),
            'p' => (string) $project->tr('problem', $locale),
            's' => (string) $project->tr('solution', $locale),
            'r' => (string) $project->tr('result', $locale),
            'quote' => (string) $project->tr('quote', $locale),
            'quoteBy' => (string) $project->quote_author,
        ];
    }

    /** @param  array<string, string>|string|null  $value */
    private function text(array|string|null $value, Locale $locale): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        return (string) ($value[$locale->value] ?? null ?: $value[Locale::main()->value] ?? '');
    }
}
