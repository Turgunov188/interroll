<?php

namespace App\Services\Site\ViewModels;

/** A short clip (Telegram, Instagram, Shorts): opens where it is published. */
final readonly class ShortVideo
{
    public function __construct(
        public string $title,
        public string $duration,
        public ?string $url,
        public ?string $poster,
    ) {}
}
