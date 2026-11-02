<?php

namespace App\Services\Site\ViewModels;

/** Small «also requested» card under a product. */
final readonly class RelatedProduct
{
    public function __construct(
        public string $code,
        public string $name,
        public string $url,
        public string $icon,
    ) {}
}
