<?php

namespace App\Services\Site\Presenters;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Models\Industry;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\Site\SiteUrl;
use App\Services\Site\ViewModels\MediaFile;
use App\Services\Site\ViewModels\ProductCard;
use App\Services\Site\ViewModels\ProductPage;
use App\Services\Site\ViewModels\RelatedProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Product model → what the catalog, product and home templates show. */
final class ProductPresenter
{
    public const FALLBACK_CATEGORY = 'maxsus';

    public function __construct(private readonly SiteUrl $urls) {}

    public function card(Product $product, Locale $locale): ProductCard
    {
        $category = $product->category?->slug ?? self::FALLBACK_CATEGORY;

        return new ProductCard(
            code: $product->sku,
            name: (string) $product->tr('name', $locale),
            url: $this->urls->product($locale, $product),
            summary: (string) $product->tr('short', $locale),
            category: $category,
            icon: $this->icon($category),
            material: $product->material->filterCode(),
            materialLabel: $product->material->label($locale),
            hardness: $product->hardnessRange() ?? '',
            hardnessMin: (int) $product->hardness,
            hardnessMax: (int) ($product->hardness_max ?: $product->hardness),
            image: $product->mainPhoto?->url(),
            industries: $product->relationLoaded('industries') ? $product->industries->pluck('slug')->all() : [],
        );
    }

    public function page(Product $product, Locale $locale): ProductPage
    {
        $product->loadMissing(['category', 'industries', 'media']);

        $name = (string) $product->tr('name', $locale);
        [$lead, $accent] = $this->splitName($name);
        $media = $product->media->groupBy(fn (ProductMedia $m): string => $m->type->value);
        $of = fn (MediaType $type): Collection => $media->get($type->value, collect());
        $model = $of(MediaType::Model3d)->first();

        return new ProductPage(
            code: $product->sku,
            name: $name,
            nameLead: $lead,
            nameAccent: $accent,
            category: (string) $product->category?->tr('name', $locale),
            description: $this->description($product, $locale),
            material: $product->material->filterCode(),
            materialLabel: $product->material->label($locale),
            hardnessLabel: $product->hardnessLabel() ?? '',
            hardness: (int) ($product->hardness ?: 90),
            configurator: $product->configurator?->value,
            parameters: $product->configurator?->parameters($locale) ?? [],
            specs: $this->specs($product, $locale),
            photos: $of(MediaType::Photo)->map(fn (ProductMedia $m): MediaFile => $this->file($m, $product, $locale))->values()->all(),
            drawings: $of(MediaType::Drawing)->map(fn (ProductMedia $m): MediaFile => $this->file($m, $product, $locale, $m->tr('alt', $locale) ?: $m->sizeLabel()))->values()->all(),
            model: $model ? $this->file($model, $product, $locale) : null,
            documents: $this->documents($product, $locale),
            applications: $product->industries->map(fn (Industry $industry): array => [
                'name' => (string) $industry->tr('name', $locale),
                'hardness' => $product->hardnessRange() ?? '',
            ])->values()->all(),
        );
    }

    public function related(Product $product, Locale $locale): RelatedProduct
    {
        return new RelatedProduct(
            code: $product->sku,
            name: (string) $product->tr('name', $locale),
            url: $this->urls->product($locale, $product),
            icon: $this->icon($product->category?->slug),
        );
    }

    /** Line drawing of the category (config/site.php → category_icons). */
    public function icon(?string $category): string
    {
        $icons = config('site.category_icons', []);

        return $icons[$category] ?? $icons[self::FALLBACK_CATEGORY] ?? '';
    }

    private function file(ProductMedia $media, Product $product, Locale $locale, ?string $title = null): MediaFile
    {
        return new MediaFile(
            url: $media->url(),
            title: (string) ($title ?? ($media->tr('alt', $locale) ?: $product->tr('name', $locale))),
            isImage: $media->isImage(),
        );
    }

    /**
     * «Документы» tab: documents first, then drawings as downloads.
     *
     * @return list<MediaFile>
     */
    private function documents(Product $product, Locale $locale): array
    {
        return $product->media
            ->filter(fn (ProductMedia $m): bool => in_array($m->type, [MediaType::Document, MediaType::Drawing], true))
            ->sortBy(fn (ProductMedia $m): array => [$m->type === MediaType::Document ? 0 : 1, $m->sort, $m->id])
            ->map(fn (ProductMedia $m): MediaFile => new MediaFile(
                url: $m->url(),
                title: (string) ($m->tr('alt', $locale) ?: pathinfo((string) $m->original_name, PATHINFO_FILENAME) ?: $m->type->getLabel()),
                isImage: $m->isImage(),
                meta: $m->sizeLabel(),
            ))
            ->values()
            ->all();
    }

    /** @return list<array{name: string, value: string}> */
    private function specs(Product $product, Locale $locale): array
    {
        return collect($product->specs ?? [])
            ->map(fn (array $spec): array => [
                'name' => $this->translated($spec['name'] ?? null, $locale),
                'value' => trim(($spec['value'] ?? '').' '.($spec['unit'] ?? '')),
            ])
            ->filter(fn (array $spec): bool => $spec['name'] !== '' && $spec['value'] !== '')
            ->values()
            ->all();
    }

    /** "Полиуретановый вал" → ["Полиуретановый", "вал"]: the last word gets the accent. */
    private function splitName(string $name): array
    {
        $pos = mb_strrpos($name, ' ');

        return $pos === false ? ['', $name] : [mb_substr($name, 0, $pos), mb_substr($name, $pos + 1)];
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
