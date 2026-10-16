<?php

namespace App\Services\Site\Presenters;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Models\Industry;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\Site\SiteUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Turns a Product into the plain arrays the site's front-end expects.
 * Keys match the catalog/product page templates (see build-tools/dynamic_patches.py).
 */
final class ProductPresenter
{
    public const FALLBACK_CATEGORY = 'maxsus';

    public function __construct(private readonly SiteUrl $urls) {}

    /** @return array<string, mixed> one card on the catalog page */
    public function card(Product $product, Locale $locale): array
    {
        return [
            'cat' => $product->category?->slug ?? self::FALLBACK_CATEGORY,
            'code' => $product->sku,
            'name' => $product->tr('name', $locale),
            'mat' => $product->material->filterCode(),
            'matL' => $product->material->label($locale),
            'shore' => $product->hardnessRange() ?? '',
            'spec' => $product->tr('short', $locale) ?? '',
            'url' => $this->urls->product($locale, $product),
            'img' => $product->mainPhoto?->url(),
            'inds' => $product->industries->pluck('slug')->all(),
        ];
    }

    /** @return array<string, mixed> the product page header, specs and media */
    public function page(Product $product, Locale $locale): array
    {
        $name = (string) $product->tr('name', $locale);
        [$first, $last] = $this->splitName($name);

        return [
            'code' => $product->sku,
            'name' => $name,
            'name1' => $first,
            'name2' => $last,
            'cat' => (string) $product->category?->tr('name', $locale),
            'desc' => $this->description($product, $locale),
            'specs' => collect($product->specs ?? [])
                ->map(fn (array $spec): array => [
                    'name' => $this->translated($spec['name'] ?? null, $locale),
                    'value' => trim(($spec['value'] ?? '').' '.($spec['unit'] ?? '')),
                ])
                ->filter(fn (array $spec): bool => $spec['name'] !== '' && $spec['value'] !== '')
                ->values()
                ->all(),
            'matL' => $product->material->label($locale),
            'shore' => $product->hardnessLabel() ?? '',
            'mat' => $product->material->filterCode(),
            'hardness' => $product->hardness,
            'configurator' => $product->configurator?->value,
            'configurable' => $product->configurator !== null,
            'params' => $product->configurator?->parameters($locale) ?? [],
            'media' => $this->media($product, $locale),
            'docs' => $this->documents($product, $locale),
            'uses' => $product->industries
                ->values()
                ->map(fn (Industry $industry, int $i): array => [
                    'n' => '/'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    't' => $industry->tr('name', $locale),
                    'd' => '',
                    's' => $product->hardnessRange() ?? '',
                ])
                ->all(),
        ];
    }

    /** @return array<string, mixed> small "also requested" card */
    public function related(Product $product, Locale $locale): array
    {
        $icons = config('site.category_icons', []);

        return [
            'code' => $product->sku,
            'name' => $product->tr('name', $locale),
            'url' => $this->urls->product($locale, $product),
            'icon' => $icons[$product->category?->slug] ?? $icons[self::FALLBACK_CATEGORY] ?? '',
        ];
    }

    /**
     * Files grouped for the three views of the product page (Чертёж / 3D / Фото).
     *
     * @return array{photos: list<array>, drawings: list<array>, model: array|null}
     */
    private function media(Product $product, Locale $locale): array
    {
        $byType = $product->media->groupBy(fn (ProductMedia $m): string => $m->type->value);
        $of = fn (MediaType $type): Collection => $byType->get($type->value, collect());
        $alt = fn (ProductMedia $m): string => (string) ($m->tr('alt', $locale) ?: $product->tr('name', $locale));
        $model = $of(MediaType::Model3d)->first();

        return [
            'photos' => $of(MediaType::Photo)
                ->map(fn (ProductMedia $m): array => ['src' => $m->url(), 'alt' => $alt($m)])
                ->values()->all(),
            'drawings' => $of(MediaType::Drawing)
                ->map(fn (ProductMedia $m): array => [
                    'src' => $m->url(),
                    'alt' => $alt($m),
                    'isImage' => $m->isImage(),
                    'label' => $m->tr('alt', $locale) ?: $m->sizeLabel(),
                ])
                ->values()->all(),
            'model' => $model ? ['src' => $model->url(), 'alt' => $alt($model)] : null,
        ];
    }

    /**
     * «Документы» tab: documents first, then drawings as downloads.
     *
     * @return list<array{title: string, url: string, meta: string}>
     */
    private function documents(Product $product, Locale $locale): array
    {
        return $product->media
            ->filter(fn (ProductMedia $m): bool => in_array($m->type, [MediaType::Document, MediaType::Drawing], true))
            ->sortBy(fn (ProductMedia $m): array => [$m->type === MediaType::Document ? 0 : 1, $m->sort, $m->id])
            ->map(fn (ProductMedia $m): array => [
                'title' => (string) ($m->tr('alt', $locale) ?: pathinfo((string) $m->original_name, PATHINFO_FILENAME) ?: $m->type->getLabel()),
                'url' => $m->url(),
                'meta' => $m->sizeLabel().' ↓',
            ])
            ->values()
            ->all();
    }

    /** "Полиуретановый вал" → ["Полиуретановый", "вал"]: the last word gets the accent. */
    private function splitName(string $name): array
    {
        $pos = mb_strrpos($name, ' ');

        return $pos === false ? [$name, ''] : [mb_substr($name, 0, $pos), mb_substr($name, $pos + 1)];
    }

    private function description(Product $product, Locale $locale): string
    {
        $text = trim(html_entity_decode(strip_tags((string) $product->tr('description', $locale))));

        return Str::squish($text !== '' ? $text : (string) $product->tr('short', $locale));
    }

    /** Spec names are stored as {"ru": "...", "uz": "..."}. */
    private function translated(mixed $value, Locale $locale): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        return (string) ($value[$locale->value] ?? $value[Locale::main()->value] ?? collect($value)->first(fn ($v) => filled($v)) ?? '');
    }
}
