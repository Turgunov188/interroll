<?php

namespace App\Services\Site\ViewModels;

/** Everything the product page shows about one product. */
final readonly class ProductPage
{
    /**
     * @param  list<array{name: string, value: string}>  $specs
     * @param  list<array{key: string, label: string, min: int, max: int, step: int, default: int, unit: string}>  $parameters
     * @param  list<MediaFile>  $photos
     * @param  list<MediaFile>  $drawings
     * @param  list<MediaFile>  $documents
     * @param  list<array{name: string, hardness: string}>  $applications
     */
    public function __construct(
        public string $code,
        public string $name,
        public string $nameLead,
        public string $nameAccent,
        public string $category,
        public string $description,
        public string $material,
        public string $materialLabel,
        public string $hardnessLabel,
        public int $hardness,
        public ?string $configurator,
        public array $parameters,
        public array $specs,
        public array $photos,
        public array $drawings,
        public ?MediaFile $model,
        public array $documents,
        public array $applications,
    ) {}

    public function isConfigurable(): bool
    {
        return $this->configurator !== null;
    }
}
