<?php

namespace App\Services\Site;

use App\Enums\Locale;
use App\Enums\SettingKey;
use App\Models\Product;
use Illuminate\Routing\Route;

/**
 * Everything a public page template needs besides its own data: the language, links,
 * navigation, site settings and translations with those settings filled in.
 * Built once per request by SiteResponse and shared with every view as $site.
 */
final class SiteContext
{
    /** @var array<string, string>|null */
    private ?array $tokens = null;

    public function __construct(
        public readonly Locale $locale,
        private readonly SiteSettings $settings,
        private readonly SiteUrl $urls,
        private readonly ?Route $route = null,
    ) {}

    // ---- Links

    /** Relative URL of a site page in the current language, e.g. url('catalog', ['cat' => 'valik']). */
    public function url(string $page, array $query = []): string
    {
        $url = $this->urls->page($this->locale, $page);

        return $query ? $url.'?'.http_build_query($query) : $url;
    }

    public function productUrl(Product|string $product): string
    {
        return $this->urls->product($this->locale, $product);
    }

    public function postUrl(string $slug): string
    {
        return $this->urls->post($this->locale, $slug);
    }

    /** The current page in every language (absolute URLs) for hreflang and the language switcher. */
    public function alternates(): array
    {
        $out = [];

        foreach (Locale::cases() as $locale) {
            $out[$locale->value] = $this->route ? $this->urls->sameRouteIn($this->route, $locale) : url($locale->path('/'));
        }

        return $out;
    }

    public function canonical(): string
    {
        return $this->alternates()[$this->locale->value];
    }

    /** Is the current request this page (any of its sub-pages: product → catalog, article → blog)? */
    public function isCurrent(string $page): bool
    {
        $current = $this->currentPage();

        return $current === $page || (config("site.sections.{$current}") === $page);
    }

    public function currentPage(): ?string
    {
        $name = $this->route?->getName();

        // site.{lang}.{page}[.default]
        return $name ? explode('.', $name)[2] ?? null : null;
    }

    /**
     * @param  'main'|'more'|'footer'  $menu
     * @return list<array{page: string, label: string, url: string, current: bool}>
     */
    public function menu(string $menu): array
    {
        return array_map(fn (string $page): array => [
            'page' => $page,
            'label' => $this->t("nav.{$page}"),
            'url' => $this->url($page),
            'current' => $this->isCurrent($page),
        ], config("site.navigation.{$menu}", []));
    }

    // ---- Settings

    /** A setting as printed on the site; a visible gap "[__]" until the admin fills it. */
    public function setting(SettingKey|string $key): string
    {
        return $this->settings->text($this->key($key), $this->locale);
    }

    public function filled(SettingKey|string $key): bool
    {
        return $this->settings->filled($this->key($key), $this->locale);
    }

    /** "tel:+998…" for a phone setting, "#" while it is empty. */
    public function tel(SettingKey|string $key = SettingKey::Phone): string
    {
        return $this->settings->tel($this->key($key)) ?? '#';
    }

    public function image(SettingKey|string $key): ?string
    {
        return $this->settings->imageUrl($this->key($key));
    }

    /** @param  'map_yandex'|'map_google'|'map_route'  $which */
    public function map(string $which): string
    {
        return $this->settings->mapLinks()[$which] ?? '#';
    }

    /** A number setting (factory statistics, hours); 0 while it is empty. */
    public function number(SettingKey|string $key): int
    {
        return (int) preg_replace('/\D+/', '', $this->settings->get($this->key($key), $this->locale));
    }

    // ---- Translations

    /**
     * Translation in the page language with the site settings filled in: a line
     * "Срок: от :lead_days дней" gets the value of «Срок изготовления». Works for
     * lists too (lang files hold FAQ, steps, comparison rows as arrays).
     *
     * @return ($key is string ? string|array : never)
     */
    public function t(string $key, array $replace = []): string|array
    {
        $line = trans($key, [], $this->locale->value);

        return $this->fill($line, $replace);
    }

    private function fill(mixed $line, array $replace): mixed
    {
        if (is_array($line)) {
            return array_map(fn ($item) => $this->fill($item, $replace), $line);
        }

        if (! is_string($line) || ! str_contains($line, ':')) {
            return $line;
        }

        $pairs = [];

        foreach ($replace + $this->tokens() as $name => $value) {
            $pairs[':'.$name] = (string) $value;
        }

        return strtr($line, $pairs);
    }

    /** @return array<string, string> */
    private function tokens(): array
    {
        return $this->tokens ??= $this->settings->tokens($this->locale);
    }

    private function key(SettingKey|string $key): SettingKey
    {
        return $key instanceof SettingKey ? $key : SettingKey::from($key);
    }
}
