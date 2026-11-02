<?php

namespace App\Services\Site\ViewModels;

/** A video of the /video player: YouTube or an uploaded file. */
final readonly class Video
{
    /** @param  list<array{at: string, label: string, position: float, seconds: int}>  $chapters */
    public function __construct(
        public string $title,
        public string $category,
        public string $duration,
        public int $seconds,
        public string $description,
        public ?string $youtubeId,
        public ?string $file,
        public ?string $poster,
        public array $chapters,
    ) {}
}
