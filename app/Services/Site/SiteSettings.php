<?php

namespace App\Services\Site;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Enums\SettingKey;
use App\Enums\SettingKind;
use App\Models\SiteSetting;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Storage;

/**
 * Site settings for the public pages, cached (they change only on the admin settings page).
 *
 * The built pages carry three placeholder forms per key (build-tools/site_tokens.py):
 *   __S_key__   inside the HTML template → HTML-escaped text
 *   __SJ_key__  inside a JS string        → JS-escaped text
 *   __SH_key__  image slot               → an <img> tag, or nothing when no photo is uploaded
 */
final class SiteSettings
{
    private const CACHE_KEY = 'site.settings';

    public function __construct(private readonly Repository $cache) {}

    /** @return array<string, mixed> raw stored values by key */
    public function all(): array
    {
        return $this->cache->rememberForever(self::CACHE_KEY, fn (): array => SiteSetting::query()->pluck('value', 'key')->all());
    }

    /** The value as printed on the site, '' when it is not filled. */
    public function get(SettingKey $key, ?Locale $locale = null): string
    {
        $value = $this->all()[$key->value] ?? null;

        if (is_array($value)) {
            $locale ??= Locale::tryFrom(app()->getLocale()) ?? Locale::main();
            $value = filled($value[$locale->value] ?? null) ? $value[$locale->value] : ($value[Locale::main()->value] ?? null);
        }

        return trim((string) $value);
    }

    /** @param  array<string, mixed>  $values  key => value, as the admin form sends them */
    public function save(array $values): void
    {
        foreach (SettingKey::cases() as $key) {
            if (array_key_exists($key->value, $values)) {
                SiteSetting::query()->updateOrCreate(['key' => $key->value], ['value' => $this->clean($values[$key->value])]);
            }
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    /** @return array<string, string> placeholder => value for SitePageRenderer */
    public function placeholders(Locale $locale): array
    {
        $out = [];

        foreach (SettingKey::cases() as $key) {
            $value = $this->get($key, $locale);
            $text = $value === '' ? $key->kind()->fallback() : $value;

            if ($key->kind() === SettingKind::Image) {
                $url = $value === '' ? '' : Storage::disk(MediaType::DISK)->url($value);
                $text = $url;
                $out["__SH_{$key->value}__"] = $url === '' ? '' : sprintf(
                    '<img src="%s" alt="" loading="lazy" style="position: absolute; inset: 0; width: 100%%; height: 100%%; object-fit: cover">',
                    e($url),
                );
            }

            $out["__S_{$key->value}__"] = $this->html($text);
            $out["__SJ_{$key->value}__"] = $this->js($text);
        }

        foreach ([SettingKey::Phone, SettingKey::SalesPhone, SettingKey::EngineerPhone, SettingKey::AccountingPhone] as $phone) {
            $digits = preg_replace('/\D+/', '', $this->get($phone, $locale)) ?? '';
            $href = $digits === '' ? '' : '+'.$digits;
            $out["__S_{$phone->value}_href__"] = $this->html($href);
            $out["__SJ_{$phone->value}_href__"] = $this->js($href);
        }

        foreach ($this->mapLinks() as $name => $url) {
            $out["__S_{$name}__"] = $this->html($url);
            $out["__SJ_{$name}__"] = $this->js($url);
        }

        return $out;
    }

    /**
     * Map links from the factory coordinates ("41.311081, 69.240562"); "#" until they are set.
     *
     * @return array{map_yandex: string, map_google: string, map_route: string}
     */
    public function mapLinks(): array
    {
        if (! preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,; ]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $this->get(SettingKey::Coordinates), $m)) {
            return ['map_yandex' => '#', 'map_google' => '#', 'map_route' => '#'];
        }

        [, $lat, $lng] = $m;

        return [
            'map_yandex' => "https://yandex.uz/maps/?pt={$lng},{$lat}&z=16&l=map",
            'map_google' => "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}",
            'map_route' => "https://yandex.uz/maps/?rtext=~{$lat},{$lng}&rtt=auto",
        ];
    }

    private function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $value);

            return array_filter($value, fn ($v) => filled($v)) ?: null;
        }

        return filled($value) ? trim((string) $value) : null;
    }

    /** Text node / attribute of the page template. "{{" would start a template expression. */
    private function html(string $value): string
    {
        return str_replace('{{', '{ {', e($value, false));
    }

    /** Inside a quoted JS string literal (any quote style). */
    private function js(string $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

        return strtr(substr($json, 1, -1), ['`' => '\\u0060', '$' => '\\u0024']);
    }
}
