<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use App\Services\Site\ContentCache;
use App\Services\Site\Presenters\ProductPresenter;
use App\Services\Site\SiteUrl;
use App\Services\Site\ViewModels\CategoryLink;
use App\Services\Site\ViewModels\IndustryProducts;
use Illuminate\Support\Str;

/**
 * Home page: the «by industry» tabs with three products each and the list of catalog sections.
 * Cached with the content pages; catalog changes flush it too (CatalogObserver).
 */
final class HomePageData implements PageData
{
    public const INDUSTRIES = 5;

    public const PRODUCTS_PER_INDUSTRY = 3;

    public const CATEGORIES = 6;

    public function __construct(
        private readonly ContentCache $cache,
        private readonly ProductPresenter $presenter,
        private readonly SiteUrl $urls,
    ) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('home', $locale, function () use ($locale): array {
            $industries = Industry::query()
                ->whereHas('products', fn ($q) => $q->published())
                ->with(['products' => fn ($q) => $q->published()->ordered()->with(['category.parent', 'machineBrand', 'mainPhoto'])])
                ->ordered()
                ->limit(self::INDUSTRIES)
                ->get();

            $categories = Category::query()
                ->active()
                ->whereHas('products', fn ($q) => $q->published())
                ->ordered()
                ->limit(self::CATEGORIES)
                ->get();

            $catalog = $this->urls->page($locale, 'catalog');

            return [
                'industries' => $industries->map(fn (Industry $industry) => new IndustryProducts(
                    slug: $industry->slug,
                    name: (string) $industry->tr('name', $locale),
                    products: $industry->products
                        ->take(self::PRODUCTS_PER_INDUSTRY)
                        ->map(fn (Product $p) => $this->presenter->card($p, $locale))
                        ->values()
                        ->all(),
                ))->all(),
                'categories' => $categories->values()->map(fn (Category $c, int $i) => new CategoryLink(
                    number: sprintf('/%02d', $i + 1),
                    name: (string) $c->tr('name', $locale),
                    description: Str::limit(strip_tags((string) $c->tr('description', $locale)), 110),
                    url: $catalog.'?'.http_build_query(['cat' => $c->slug]),
                ))->all(),
            ];
        });
    }
}
