<?php

namespace App\Services\Site\ViewModels;

/** A product in a list: catalog grid, home page industry tabs. */
final readonly class ProductCard
{
    /** @param  list<string>  $industries  industry slugs, for the catalog filter */
    public function __construct(
        public string $code,
        public string $name,
        public string $url,
        public string $summary,
        public string $category,
        public string $icon,
        public string $material,
        public string $materialLabel,
        public string $hardness,
        public int $hardnessMin,
        public int $hardnessMax,
        public ?string $image,
        public array $industries,
    ) {}

    /**
     * What the catalog script needs to know about the card: filters and the request cart
     * (resources/js/site/pages/catalog.js).
     *
     * @return array{code: string, name: string, icon: string, category: string, material: string, hardness: array{0: int, 1: int}, industries: list<string>, text: string}
     */
    public function forScript(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'icon' => $this->icon,
            'category' => $this->category,
            'material' => $this->material,
            'hardness' => [$this->hardnessMin, $this->hardnessMax],
            'industries' => $this->industries,
            'text' => mb_strtolower($this->code.' '.$this->name.' '.$this->summary),
        ];
    }
}
