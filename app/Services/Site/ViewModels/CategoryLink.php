<?php

namespace App\Services\Site\ViewModels;

/** A catalog section in the home page list. */
final readonly class CategoryLink
{
    public function __construct(
        public string $number,
        public string $name,
        public string $description,
        public string $url,
    ) {}
}
