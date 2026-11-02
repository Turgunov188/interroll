<?php

namespace App\Services\Site\ViewModels;

/** A finished job on the «Работы» page: problem → solution → result. */
final readonly class ProjectCase
{
    /** @param  list<array{value: string, label: string}>  $kpis */
    public function __construct(
        public string $slug,
        public string $industry,
        public string $industryName,
        public string $client,
        public string $title,
        public string $summary,
        public ?string $cover,
        public ?string $before,
        public ?string $after,
        public array $kpis,
        public string $problem,
        public string $solution,
        public string $result,
        public string $quote,
        public string $quoteAuthor,
    ) {}
}
