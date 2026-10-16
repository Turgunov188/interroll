<?php

namespace App\Services\Site;

use App\Enums\Locale;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * Reads a built page (plain HTML, not Blade: its templates use {{ }} for the
 * front-end renderer) and fills the server-side placeholders.
 */
final class SitePageRenderer
{
    public const CSRF_TOKEN = '__CSRF_TOKEN__';

    public const BASE_URL = '__BASE__';

    /** JSON read by the page as window.__IR (null on pages without server data). */
    public const DATA = '__IR_DATA__';

    /** Tail of the canonical / language-switch URLs, e.g. "/ir-v01" on product pages. */
    public const URL_SUFFIX = '__SUFFIX__';

    /** <title> of pages that show one record (product, article). */
    public const PAGE_TITLE = '__PAGE_TITLE__';

    public function __construct(
        private readonly Filesystem $files,
        private readonly string $directory,
    ) {}

    public function exists(Locale $locale, string $page): bool
    {
        return $this->isSafeName($page) && $this->files->isFile($this->path($locale, $page));
    }

    /**
     * @param  array<string, string>  $placeholders  e.g. [SitePageRenderer::CSRF_TOKEN => csrf_token()]
     *
     * @throws FileNotFoundException
     */
    public function render(Locale $locale, string $page, array $placeholders = []): string
    {
        if (! $this->exists($locale, $page)) {
            throw new FileNotFoundException("Site page [{$locale->value}/{$page}] does not exist.");
        }

        return strtr($this->files->get($this->path($locale, $page)), $placeholders);
    }

    private function path(Locale $locale, string $page): string
    {
        return $this->directory.DIRECTORY_SEPARATOR.$locale->value.DIRECTORY_SEPARATOR.$page.'.html';
    }

    /** Page names come from config, but never let a value escape the directory. */
    private function isSafeName(string $page): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $page);
    }
}
