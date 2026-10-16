<?php

namespace App\Data;

/** One part the customer asked for: catalog code, quantity and the sizes picked in the configurator. */
final readonly class LeadItemData
{
    /** @param  array<string, string|int|float>  $options  configurator values by key (mat, shore, D, L…) */
    public function __construct(
        public string $code,
        public int $quantity = 1,
        public array $options = [],
    ) {}
}
