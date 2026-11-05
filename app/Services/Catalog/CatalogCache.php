<?php

namespace App\Services\Catalog;

use App\Enums\Locale;
use App\Services\Site\ContentCache;
use Closure;
use Illuminate\Contracts\Cache\Repository;

/**
 * The public catalog is read on every visit but changes only from the admin panel,
 * so it is cached per language and flushed by CatalogObserver on any change.
 */
final class CatalogCache
{
    /** Bump the version whenever the cached data changes shape, so a deploy never serves the old one. */
    private const PREFIX = 'site.v3.catalog.';

    /** Everything built from the catalog; each is cached per language. */
    private const NAMES = ['catalog', 'part-finder'];

    public function __construct(
        private readonly Repository $cache,
        private readonly ContentCache $content,
    ) {}

    /**
     * @param  Closure(): array<string, mixed>  $build
     * @return array<string, mixed>
     */
    public function remember(Locale $locale, Closure $build, string $name = 'catalog'): array
    {
        return $this->cache->rememberForever($this->key($name, $locale), $build);
    }

    public function flush(): void
    {
        foreach (self::NAMES as $name) {
            foreach (Locale::cases() as $locale) {
                $this->cache->forget($this->key($name, $locale));
            }
        }

        // the home page shows products and sections too
        $this->content->flush();
    }

    private function key(string $name, Locale $locale): string
    {
        return self::PREFIX.$name.'.'.$locale->value;
    }
}
