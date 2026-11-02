<?php

namespace App\Services\Site\ViewModels;

final readonly class Certificate
{
    public function __construct(
        public string $title,
        public string $details,
        public ?string $url,
        public ?string $preview,
    ) {}
}
