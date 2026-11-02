<?php

namespace App\Services\Site\ViewModels;

/** Home page «by industry» tab: an industry and a few of its products. */
final readonly class IndustryProducts
{
    /** @param  list<ProductCard>  $products */
    public function __construct(
        public string $slug,
        public string $name,
        public array $products,
    ) {}
}
