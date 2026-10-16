<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Enums\VideoKind;
use App\Models\Video;
use App\Services\Site\ContentCache;

/** /video: the player with its playlist and the short clips (build-tools/dynamic_patches.py → video). */
final class VideosPageData implements PageData
{
    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('videos', $locale, function () use ($locale): array {
            $videos = Video::query()->published()->ordered()->get();

            return [
                'videos' => $videos->where('kind', VideoKind::Main)->values()->map(fn (Video $v): array => $this->main($v, $locale))->all(),
                'shorts' => $videos->where('kind', VideoKind::Short)->values()->map(fn (Video $v): array => [
                    't' => (string) $v->tr('title', $locale),
                    'len' => (string) $v->duration,
                    'url' => $v->url ?: $v->fileUrl('file'),
                    'poster' => $v->fileUrl('poster') ?? $this->youtubePoster($v),
                ])->all(),
            ];
        });
    }

    /** @return array<string, mixed> */
    private function main(Video $video, Locale $locale): array
    {
        $length = max(1, Video::seconds($video->duration));

        return [
            't' => (string) $video->tr('title', $locale),
            'cat' => (string) $video->tr('category', $locale),
            'len' => (string) $video->duration,
            'sec' => $length,
            'd' => (string) $video->tr('description', $locale),
            'yt' => $video->file ? null : $video->youtubeId(),
            'file' => $video->fileUrl('file'),
            'poster' => $video->fileUrl('poster') ?? $this->youtubePoster($video),
            // [time, title, position 0…1 on the progress bar, seconds] — the design's chapter tuple + seconds
            'chapters' => collect($video->chapters ?? [])
                ->map(function (array $c) use ($locale, $length): array {
                    $label = is_array($c['label'] ?? null) ? ($c['label'][$locale->value] ?? null ?: $c['label'][Locale::main()->value] ?? '') : (string) ($c['label'] ?? '');
                    $at = Video::seconds($c['at'] ?? null);

                    return [(string) ($c['at'] ?? ''), (string) $label, min(1, $at / $length), $at];
                })
                ->filter(fn (array $c): bool => $c[0] !== '')
                ->values()
                ->all(),
        ];
    }

    private function youtubePoster(Video $video): ?string
    {
        $id = $video->youtubeId();

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : null;
    }
}
