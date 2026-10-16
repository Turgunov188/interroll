<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use App\Services\Site\PageData\PageData;
use App\Services\Site\SiteResponse;
use Illuminate\Http\Response;

/** Public site pages. Routes pass the page and language as route defaults (routes/web.php). */
class SiteController extends Controller
{
    public function show(string $page, string $lang, SiteResponse $response): Response
    {
        $locale = Locale::tryFrom($lang) ?? abort(404);

        return $response->page($locale, $page, $this->dataFor($page)?->for($locale));
    }

    /** Pages with server data are listed in config/site.php ("data"). */
    private function dataFor(string $page): ?PageData
    {
        $class = config("site.data.{$page}");

        return $class ? app($class) : null;
    }
}
