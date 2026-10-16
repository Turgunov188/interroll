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
    private const PREFIX = 'site.catalog.';

    public function __construct(
        private readonly Repository $cache,
        private readonly ContentCache $content,
    ) {}

    /**
     * @param  Closure(): array<string, mixed>  $build
     * @return array<string, mixed>
     */
    public function remember(Locale $locale, Closure $build): array
    {
        return $this->cache->rememberForever(self::PREFIX.$locale->value, $build);
    }

    public function flush(): void
    {
        foreach (Locale::cases() as $locale) {
            $this->cache->forget(self::PREFIX.$locale->value);
        }

        // the home page shows products and sections too
        $this->content->flush();
    }
}
