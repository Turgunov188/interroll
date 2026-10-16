<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use App\Models\Product;
use App\Services\Site\PageData\ProductPageData;
use App\Services\Site\SiteResponse;
use App\Services\Site\SiteUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/** /product/{slug} — one product from the catalog, rendered with the product page design. */
class ProductPageController extends Controller
{
    // Route parameters arrive in order: {slug} from the URI, then the "lang" default.
    public function show(string $slug, string $lang, SiteResponse $response, ProductPageData $data): Response
    {
        $locale = Locale::tryFrom($lang) ?? abort(404);

        $product = Product::query()->published()->where('slug', $slug)->firstOrFail();

        return $response->page($locale, config('site.product.page'), $data->for($product, $locale), '/'.$product->slug, $product->tr('name', $locale));
    }

    /** Plain /product (without a slug) open the default product. */
    public function defaultProduct(string $lang, SiteUrl $urls): RedirectResponse
    {
        $locale = Locale::tryFrom($lang) ?? abort(404);

        return redirect($urls->product($locale, config('site.product.default')));
    }
}
