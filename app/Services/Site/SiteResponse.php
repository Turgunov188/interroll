<?php

namespace App\Services\Site;

use App\Enums\Locale;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

/** Builds the HTTP response for a public site page. */
final class SiteResponse
{
    private const JSON_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

    public function __construct(
        private readonly SitePageRenderer $pages,
        private readonly SiteSettings $settings,
    ) {}

    /**
     * @param  array<string, mixed>|null  $data  becomes window.__IR on the page
     * @param  string  $urlSuffix  appended to canonical / hreflang / language links
     * @param  string|null  $title  fills __PAGE_TITLE__ in <title> (product or article name)
     */
    public function page(Locale $locale, string $page, ?array $data = null, string $urlSuffix = '', ?string $title = null): Response
    {
        abort_unless($this->pages->exists($locale, $page), 404);

        App::setLocale($locale->value);

        $html = $this->pages->render($locale, $page, $this->settings->placeholders($locale) + [
            SitePageRenderer::CSRF_TOKEN => csrf_token(),
            SitePageRenderer::BASE_URL => rtrim(url('/'), '/'),
            SitePageRenderer::DATA => json_encode($data, self::JSON_FLAGS),
            SitePageRenderer::URL_SUFFIX => $urlSuffix,
            SitePageRenderer::PAGE_TITLE => e($title ?? 'Inter Roll'),
        ]);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Language', $locale->value)
            ->header('Cache-Control', 'no-cache, private');
    }
}
