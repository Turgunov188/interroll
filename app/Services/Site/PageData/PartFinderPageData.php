<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use App\Services\Site\Presenters\ProductPresenter;

/**
 * Part finder: products suggested for each part type of the first step
 * (config/site.php → part_finder.products). Products hidden in the admin are left out.
 */
final class PartFinderPageData implements PageData
{
    public function __construct(
        private readonly CatalogCache $cache,
        private readonly ProductPresenter $presenter,
    ) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember($locale, function () use ($locale): array {
            $map = config('site.part_finder.products', []);

            $products = Product::query()
                ->published()
                ->with('category')
                ->whereIn('sku', collect($map)->flatten()->unique())
                ->get()
                ->keyBy('sku');

            return [
                'suggestions' => collect($map)
                    ->map(fn (array $skus): array => collect($skus)
                        ->map(fn (string $sku) => $products->get($sku))
                        ->filter()
                        ->map(fn (Product $p) => $this->presenter->related($p, $locale))
                        ->values()
                        ->all())
                    ->all(),
            ];
        }, 'part-finder');
    }
}
