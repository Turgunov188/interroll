<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Product;
use App\Services\Site\Presenters\ProductPresenter;

/** Product page: the product and a few others from the same category. */
final class ProductPageData
{
    public const RELATED_LIMIT = 3;

    public function __construct(private readonly ProductPresenter $presenter) {}

    /** @return array<string, mixed> */
    public function for(Product $product, Locale $locale): array
    {
        $product->loadMissing(['category', 'industries', 'media']);

        $related = Product::query()
            ->published()
            ->whereKeyNot($product->getKey())
            ->orderByRaw('category_id = ? desc', [$product->category_id])
            ->ordered()
            ->with('category')
            ->limit(self::RELATED_LIMIT)
            ->get();

        return [
            'product' => $this->presenter->page($product, $locale),
            'related' => $related->map(fn (Product $p): array => $this->presenter->related($p, $locale))->all(),
        ];
    }
}
