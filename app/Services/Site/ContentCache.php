<?php

namespace App\Services\Site;

use App\Enums\Locale;
use Closure;
use Illuminate\Contracts\Cache\Repository;

/**
 * Cache for the content pages (projects, blog, videos, home blocks). They change only
 * from the admin panel, so they are kept until ContentObserver flushes them. Flushing bumps
 * a version number instead of deleting keys, so every content page is renewed at once.
 */
final class ContentCache
{
    private const VERSION = 'site.content.version';

    public function __construct(private readonly Repository $cache) {}

    /**
     * @param  Closure(): array<string, mixed>  $build
     * @return array<string, mixed>
     */
    public function remember(string $name, Locale $locale, Closure $build): array
    {
        $version = (int) $this->cache->get(self::VERSION, 1);

        return $this->cache->remember("site.content.{$version}.{$name}.{$locale->value}", now()->addDay(), $build);
    }

    public function flush(): void
    {
        $this->cache->forever(self::VERSION, (int) $this->cache->get(self::VERSION, 1) + 1);
    }
}
