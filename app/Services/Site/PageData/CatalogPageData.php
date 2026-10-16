<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use App\Services\Site\Presenters\ProductPresenter;

/** Catalog page: category tabs, industry filter and all published products. */
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

            $industryIds = $products->flatMap(fn (Product $p) => $p->industries->modelKeys())->unique();
            $industries = Industry::query()->whereIn('id', $industryIds)->ordered()->get();

            return [
                'industries' => $industries->map(fn (Industry $i): array => [$i->slug, $i->tr('name', $locale)])->all(),
                'cats' => $categories->map(fn (Category $c): array => [$c->slug, $c->tr('name', $locale)])->all(),
                'products' => $products->map(fn (Product $p): array => $this->presenter->card($p, $locale))->all(),
            ];
        });
    }
}
