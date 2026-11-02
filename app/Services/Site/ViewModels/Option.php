<?php

namespace App\Services\Site\ViewModels;

/** One choice of a page filter: a catalog category, an industry, a blog rubric. */
final readonly class Option
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}
}
