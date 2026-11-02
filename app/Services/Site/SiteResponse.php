<?php

namespace App\Services\Site;

use App\Enums\Locale;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

/**
 * Renders a public site page: resources/views/site/pages/{page}.blade.php with its data,
 * the shared $site context (language, links, settings, translations) and the language headers.
 */
final class SiteResponse
{
    public function __construct(
        private readonly ViewFactory $views,
        private readonly SiteSettings $settings,
        private readonly SiteUrl $urls,
        private readonly Request $request,
    ) {}

    /** @param  array<string, mixed>  $data  view variables of the page (see PageData) */
    public function page(Locale $locale, string $page, array $data = []): Response
    {
        $view = "site.pages.{$page}";
        abort_unless($this->views->exists($view), 404);

        App::setLocale($locale->value);

        $site = new SiteContext($locale, $this->settings, $this->urls, $this->request->route());
        App::instance(SiteContext::class, $site);
        $this->views->share('site', $site);

        // a View (not a string), so tests can read the page data with viewData()
        return response($this->views->make($view, $data))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Language', $locale->value)
            ->header('Cache-Control', 'no-cache, private');
    }
}
