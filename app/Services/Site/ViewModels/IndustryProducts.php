<?php

namespace App\Services\Site\ViewModels;

/** Home page «by industry» tab: an industry, a few of its products and its catalog link. */
final readonly class IndustryProducts
{
    /** @param  list<ProductCard>  $products */
    public function __construct(
        public string $slug,
        public string $name,
        public array $products,
        public string $url = '',
    ) {}

    /** Empty slots in the three-card row, filled by the «other part» card. */
    public function missing(int $slots = 3): int
    {
        return max(0, $slots - count($this->products));
    }
}
