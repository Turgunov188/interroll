<?php

namespace App\Services\Site;

use App\Enums\Locale;
use App\Models\Product;
use Illuminate\Routing\Route;

/**
 * URLs of the public site. Every page route is named "site.{lang}.{page}" (routes/web.php),
 * so a page in another language is the same name with the other language.
 */
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

    /** Absolute URL of the given site route in another language (same page, same product or article). */
    public function sameRouteIn(Route $route, Locale $locale): string
    {
        $parts = explode('.', (string) $route->getName(), 3);

        if (count($parts) < 3 || $parts[0] !== 'site') {
            return url($locale->path('/'));
        }

        // only the URI parameters ({slug}): the route defaults (page, lang) would end up in the query string
        $parameters = array_intersect_key($route->parameters(), array_flip($route->parameterNames()));

        return route("site.{$locale->value}.{$parts[2]}", $parameters);
    }
}
