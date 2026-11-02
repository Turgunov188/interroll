<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Enums\VideoKind;
use App\Models\Video;
use App\Services\Site\ContentCache;
use App\Services\Site\ViewModels\ShortVideo;
use App\Services\Site\ViewModels\Video as VideoItem;

/** /video: the player with its playlist and the short clips. */
final class VideosPageData implements PageData
{
    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('videos', $locale, function () use ($locale): array {
            $videos = Video::query()->published()->ordered()->get();

            return [
                'videos' => $videos->where('kind', VideoKind::Main)->values()->map(fn (Video $v) => $this->main($v, $locale))->all(),
                'shorts' => $videos->where('kind', VideoKind::Short)->values()->map(fn (Video $v) => new ShortVideo(
                    title: (string) $v->tr('title', $locale),
                    duration: (string) $v->duration,
                    url: $v->url ?: $v->fileUrl('file'),
                    poster: $v->fileUrl('poster') ?? $this->youtubePoster($v),
                ))->all(),
            ];
        });
    }

    private function main(Video $video, Locale $locale): VideoItem
    {
        $length = max(1, Video::seconds($video->duration));

        return new VideoItem(
            title: (string) $video->tr('title', $locale),
            category: (string) $video->tr('category', $locale),
            duration: (string) $video->duration,
            seconds: $length,
            description: (string) $video->tr('description', $locale),
            youtubeId: $video->file ? null : $video->youtubeId(),
            file: $video->fileUrl('file'),
            poster: $video->fileUrl('poster') ?? $this->youtubePoster($video),
            chapters: collect($video->chapters ?? [])
                ->map(function (array $c) use ($locale, $length): array {
                    $label = is_array($c['label'] ?? null)
                        ? (($c['label'][$locale->value] ?? null) ?: ($c['label'][Locale::main()->value] ?? ''))
                        : (string) ($c['label'] ?? '');
                    $at = Video::seconds($c['at'] ?? null);

                    return ['at' => (string) ($c['at'] ?? ''), 'label' => (string) $label, 'position' => min(1, $at / $length), 'seconds' => $at];
                })
                ->filter(fn (array $c): bool => $c['at'] !== '')
                ->values()
                ->all(),
        );
    }

    private function youtubePoster(Video $video): ?string
    {
        $id = $video->youtubeId();

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : null;
    }
}
