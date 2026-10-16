<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use App\Services\Site\ContentCache;
use App\Services\Site\SiteUrl;
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
        private readonly SiteUrl $urls,
    ) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('home', $locale, function () use ($locale): array {
            $industries = Industry::query()
                ->whereHas('products', fn ($q) => $q->published())
                ->with(['products' => fn ($q) => $q->published()->ordered()->with('mainPhoto')])
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
                'inds' => $industries->map(fn (Industry $industry): array => [
                    'k' => $industry->slug,
                    'label' => $industry->tr('name', $locale),
                    'products' => $industry->products->take(self::PRODUCTS_PER_INDUSTRY)->map(fn (Product $p): array => [
                        'code' => $p->sku,
                        'name' => (string) $p->tr('name', $locale),
                        'desc' => (string) $p->tr('short', $locale),
                        'url' => $this->urls->product($locale, $p),
                        'img' => $p->mainPhoto?->url(),
                    ])->values()->all(),
                ])->all(),
                'cats' => $categories->values()->map(fn (Category $c, int $i): array => [
                    'n' => '/'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'name' => (string) $c->tr('name', $locale),
                    'desc' => Str::limit(strip_tags((string) $c->tr('description', $locale)), 110),
                    'url' => $catalog.'?cat='.$c->slug,
                ])->all(),
            ];
        });
    }
}
