<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Testimonial;
use App\Services\Site\ContentCache;
use App\Services\Site\ViewModels\Certificate as CertificateCard;
use App\Services\Site\ViewModels\ClientLogo;
use App\Services\Site\ViewModels\Testimonial as Review;

/** Factory page, «Нам доверяют»: client logos, reviews, certificates. */
final class FactoryPageData implements PageData
{
    public function __construct(private readonly ContentCache $cache) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('factory', $locale, fn (): array => [
            'clients' => Client::query()->published()->ordered()->get()
                ->map(fn (Client $c) => new ClientLogo($c->name, $c->fileUrl('logo')))->all(),
            'testimonials' => Testimonial::query()->published()->ordered()->get()
                ->map(fn (Testimonial $t) => new Review((string) $t->tr('text', $locale), (string) $t->author, $t->fileUrl('photo')))->all(),
            'certificates' => Certificate::query()->published()->ordered()->get()
                ->map(fn (Certificate $c) => new CertificateCard(
                    title: (string) $c->tr('title', $locale),
                    details: collect([
                        $c->number ? '№ '.$c->number : null,
                        $c->valid_until ? '→ '.$c->valid_until->format('d.m.Y') : null,
                    ])->filter()->implode(' · '),
                    url: $c->fileUrl('file'),
                    preview: $c->file && preg_match('/\.(jpe?g|png|webp)$/i', $c->file) ? $c->fileUrl('file') : null,
                ))->all(),
        ]);
    }
}
