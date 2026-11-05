<?php

namespace App\Services\Site\ViewModels;

/** A product in a list: catalog grid, home page industry tabs. */
final readonly class ProductCard
{
    /**
     * @param  string  $category  top-level section (catalog tab)
     * @param  string  $type  sub-section inside it, '' when the product sits in the top-level section itself
     * @param  string  $machine  slug of the machine make the part fits, '' for parts made to a drawing
     * @param  list<string>  $industries  industry slugs, for the catalog filter
     */
    public function __construct(
        public string $code,
        public string $name,
        public string $url,
        public string $summary,
        public string $category,
        public string $type,
        public string $machine,
        public string $machineName,
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
     * @return array{code: string, name: string, icon: string, category: string, type: string, machine: string, material: string, hardness: array{0: int, 1: int}, industries: list<string>, text: string}
     */
    public function forScript(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'icon' => $this->icon,
            'category' => $this->category,
            'type' => $this->type,
            'machine' => $this->machine,
            'material' => $this->material,
            'hardness' => [$this->hardnessMin, $this->hardnessMax],
            'industries' => $this->industries,
            'text' => mb_strtolower(implode(' ', [$this->code, $this->name, $this->machineName, $this->summary])),
        ];
    }
}
