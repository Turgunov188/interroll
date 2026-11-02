<?php

namespace App\Services\Site\ViewModels;

/** A blog article in the list. */
final readonly class PostCard
{
    public function __construct(
        public string $slug,
        public string $category,
        public string $categoryName,
        public string $title,
        public string $excerpt,
        public int $minutes,
        public string $coverCaption,
        public ?string $cover,
        public string $url,
    ) {}
}
