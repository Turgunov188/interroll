<?php

namespace App\Services\Site;

use App\Enums\Locale;
use App\Models\Product;

/** Relative URLs for the public site in a given language. */
final class SiteUrl
{
    public function page(Locale $locale, string $page): string
    {
        return route("site.{$locale->value}.{$page}", [], false);
    }

    public function product(Locale $locale, Product|string $product): string
    {
        $slug = $product instanceof Product ? $product->slug : $product;

        return route("site.{$locale->value}.product", ['slug' => $slug], false);
    }

    public function post(Locale $locale, string $slug): string
    {
        return route("site.{$locale->value}.post", ['slug' => $slug], false);
    }
}
