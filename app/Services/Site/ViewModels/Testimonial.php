<?php

namespace App\Services\Site\ViewModels;

final readonly class Testimonial
{
    public function __construct(
        public string $text,
        public string $author,
        public ?string $photo,
    ) {}
}
