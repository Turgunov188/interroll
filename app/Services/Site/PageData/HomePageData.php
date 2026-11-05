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
use App\Services\Site\ViewModels\ProductCard;
use Illuminate\Support\Str;

/**
 * Home page: the «by industry» tabs with three products each and the list of catalog sections.
 * Each tab shows the products marked «На главной» first, then random ones from that industry,
 * so the page changes on every visit. The candidate lists are cached with the content pages
 * (catalog changes flush them, CatalogObserver); the pick happens per request.
 */
final class HomePageData implements PageData
{
    public const INDUSTRIES = 5;

    public const PRODUCTS_PER_INDUSTRY = 3;

    public const CATEGORIES = 6;

    /** How many non-featured products per industry are kept as random candidates. */
    public const CANDIDATES = 30;

    public function __construct(
        private readonly ContentCache $cache,
        private readonly ProductPresenter $presenter,
        private readonly SiteUrl $urls,
    ) {}

    public function for(Locale $locale): array
    {
        $data = $this->cache->remember('home', $locale, function () use ($locale): array {
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
            $cards = fn ($products) => $products->map(fn (Product $p) => $this->presenter->card($p, $locale))->values()->all();

            return [
                'industries' => $industries->map(function (Industry $industry) use ($locale, $cards): array {
                    [$featured, $others] = $industry->products->partition(fn (Product $p) => $p->is_featured);

                    return [
                        'slug' => $industry->slug,
                        'name' => (string) $industry->tr('name', $locale),
                        'featured' => $cards($featured),
                        'others' => $cards($others->take(self::CANDIDATES)),
                    ];
                })->all(),
                'categories' => $categories->values()->map(fn (Category $c, int $i) => new CategoryLink(
                    number: sprintf('/%02d', $i + 1),
                    name: (string) $c->tr('name', $locale),
                    description: Str::limit(strip_tags((string) $c->tr('description', $locale)), 110),
                    url: $catalog.'?'.http_build_query(['cat' => $c->slug]),
                ))->all(),
            ];
        });

        $data['industries'] = array_map(fn (array $tab) => new IndustryProducts(
            slug: $tab['slug'],
            name: $tab['name'],
            products: $this->pick($tab['featured'], $tab['others']),
        ), $data['industries']);

        return $data;
    }

    /**
     * Featured products first (random among them if there are more than fit), then random others.
     *
     * @param  list<ProductCard>  $featured
     * @param  list<ProductCard>  $others
     * @return list<ProductCard>
     */
    private function pick(array $featured, array $others): array
    {
        shuffle($featured);
        shuffle($others);

        return array_slice([...$featured, ...$others], 0, self::PRODUCTS_PER_INDUSTRY);
    }
}
