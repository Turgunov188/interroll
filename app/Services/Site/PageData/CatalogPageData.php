<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use App\Services\Site\Presenters\ProductPresenter;
use App\Services\Site\ViewModels\Option;

/**
 * Catalog: every published product with the category tabs and the industry filter.
 * Filtering happens in the browser over the rendered cards (resources/js/site/pages/catalog.js).
 */
final class CatalogPageData implements PageData
{
    public function __construct(
        private readonly CatalogCache $cache,
        private readonly ProductPresenter $presenter,
    ) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember($locale, function () use ($locale): array {
            $products = Product::query()->published()->with(['category', 'mainPhoto', 'industries'])->ordered()->get();

            $categories = Category::query()
                ->active()
                ->whereIn('id', $products->pluck('category_id')->unique())
                ->ordered()
                ->get();

            $industries = Industry::query()
                ->whereIn('id', $products->flatMap(fn (Product $p) => $p->industries->map->getKey())->unique())
                ->ordered()
                ->get();

            return [
                'products' => $products->map(fn (Product $p) => $this->presenter->card($p, $locale))->all(),
                'categories' => $categories->map(fn (Category $c) => new Option($c->slug, (string) $c->tr('name', $locale)))->all(),
                'industries' => $industries->map(fn (Industry $i) => new Option($i->slug, (string) $i->tr('name', $locale)))->all(),
            ];
        });
    }
}
