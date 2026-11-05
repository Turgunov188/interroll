<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Enums\Material;
use App\Models\Category;
use App\Models\Industry;
use App\Models\MachineBrand;
use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use App\Services\Site\Presenters\ProductPresenter;
use App\Services\Site\ViewModels\Option;
use Illuminate\Support\Collection;

/**
 * Catalog: every published product with the section tabs, the part types inside a section,
 * and the machine, material and industry filters.
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
            $products = Product::query()
                ->published()
                ->with(['category.parent', 'machineBrand', 'mainPhoto', 'industries'])
                ->ordered()
                ->get();

            $used = Category::query()->active()->with('parent')->whereIn('id', $products->pluck('category_id')->unique())->get();
            $sections = Category::query()
                ->active()
                ->whereIn('id', $used->map(fn (Category $c) => $c->parent_id ?? $c->getKey())->unique())
                ->ordered()
                ->get();

            $industries = Industry::query()
                ->whereIn('id', $products->flatMap(fn (Product $p) => $p->industries->map->getKey())->unique())
                ->ordered()
                ->get();

            $machines = MachineBrand::query()
                ->whereIn('id', $products->pluck('machine_brand_id')->filter()->unique())
                ->ordered()
                ->get();

            return [
                'products' => $products->map(fn (Product $p) => $this->presenter->card($p, $locale))->all(),
                'categories' => $sections->map(fn (Category $c) => $this->option($c, $locale))->all(),
                'types' => $this->types($used, $locale),
                'machines' => $machines->map(fn (MachineBrand $m) => new Option($m->slug, $m->name))->all(),
                'industries' => $industries->map(fn (Industry $i) => new Option($i->slug, (string) $i->tr('name', $locale)))->all(),
                'silicone' => $products->contains(fn (Product $p) => $p->material === Material::Silicone),
            ];
        });
    }

    /**
     * Sub-sections that hold products, grouped by their section: section slug => options.
     *
     * @param  Collection<int, Category>  $used
     * @return array<string, list<Option>>
     */
    private function types(Collection $used, Locale $locale): array
    {
        return $used
            ->filter(fn (Category $c) => $c->parent_id !== null && $c->parent?->is_active)
            ->sortBy([['sort', 'asc'], ['id', 'asc']])
            ->groupBy(fn (Category $c) => $c->parent->slug)
            ->map(fn (Collection $children) => $children->map(fn (Category $c) => $this->option($c, $locale))->values()->all())
            ->all();
    }

    private function option(Category $category, Locale $locale): Option
    {
        return new Option($category->slug, (string) $category->tr('name', $locale));
    }
}
