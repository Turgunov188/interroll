<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Testimonial;
use App\Services\Site\ContentCache;

/** Factory page, «Нам доверяют»: client logos, reviews, certificates. */
final class FactoryPageData implements PageData
{
    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('factory', $locale, fn (): array => [
            'logos' => Client::query()->published()->ordered()->get()
                ->map(fn (Client $c): array => ['name' => $c->name, 'img' => $c->fileUrl('logo')])->all(),
            'quotes' => Testimonial::query()->published()->ordered()->get()
                ->map(fn (Testimonial $t): array => ['t' => (string) $t->tr('text', $locale), 'a' => $t->author, 'img' => $t->fileUrl('photo')])->all(),
            'certs' => Certificate::query()->published()->ordered()->get()
                ->map(fn (Certificate $c): array => [
                    't' => (string) $c->tr('title', $locale),
                    'd' => collect([$c->number ? '№ '.$c->number : null, $c->valid_until?->format('d.m.Y') ? '→ '.$c->valid_until->format('d.m.Y') : null])->filter()->implode(' · '),
                    'url' => $c->fileUrl('file'),
                    'img' => $c->file && preg_match('/\.(jpe?g|png|webp)$/i', $c->file) ? $c->fileUrl('file') : null,
                ])->all(),
        ]);
    }
}
