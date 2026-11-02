<?php

namespace App\Services\Site\ViewModels;

/** A blog article page. The body is sanitized HTML (App\Support\SafeHtml). */
final readonly class PostPage
{
    /** @param  list<array{id: string, title: string}>  $toc */
    public function __construct(
        public PostCard $card,
        public string $author,
        public string $body,
        public array $toc,
        public ?string $date,
    ) {}
}
