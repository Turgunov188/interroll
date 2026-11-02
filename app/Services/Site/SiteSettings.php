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
 * Site settings for the public pages (contacts, hours, production numbers, photos), cached:
 * they change only on the admin page «Настройки сайта».
 *
 * Templates read them through SiteContext; translations reference them as :key
 * (e.g. "от :lead_days дней"), see SiteContext::t().
 */
final class SiteSettings
{
    private const CACHE_KEY = 'site.settings';

    /** Keys with a phone number: each also gets a ready "tel:" value, ":phone_href". */
    public const PHONES = [SettingKey::Phone, SettingKey::SalesPhone, SettingKey::EngineerPhone, SettingKey::AccountingPhone];

    public function __construct(private readonly Repository $cache) {}

    /** @return array<string, mixed> raw stored values by key */
    public function all(): array
    {
        return $this->cache->rememberForever(self::CACHE_KEY, fn (): array => SiteSetting::query()->pluck('value', 'key')->all());
    }

    /** The stored value in the given language (falls back to Russian), '' when it is not filled. */
    public function get(SettingKey $key, ?Locale $locale = null): string
    {
        $value = $this->all()[$key->value] ?? null;

        if (is_array($value)) {
            $locale ??= Locale::tryFrom(app()->getLocale()) ?? Locale::main();
            $value = filled($value[$locale->value] ?? null) ? $value[$locale->value] : ($value[Locale::main()->value] ?? null);
        }

        return trim((string) $value);
    }

    /** As printed on the site: the value, or a visible gap ("[__]") while the admin has not filled it. */
    public function text(SettingKey $key, ?Locale $locale = null): string
    {
        $value = $this->get($key, $locale);

        return $value === '' ? $key->kind()->fallback() : $value;
    }

    public function filled(SettingKey $key, ?Locale $locale = null): bool
    {
        return $this->get($key, $locale) !== '';
    }

    /** "+998 71 200-00-00" → "tel:+998712000000"; null while the number is not filled. */
    public function tel(SettingKey $key): ?string
    {
        $digits = preg_replace('/\D+/', '', $this->get($key)) ?? '';

        return $digits === '' ? null : 'tel:+'.$digits;
    }

    /** Public URL of an uploaded photo setting. */
    public function imageUrl(SettingKey $key): ?string
    {
        if ($key->kind() !== SettingKind::Image || ($path = $this->get($key)) === '') {
            return null;
        }

        return Storage::disk(MediaType::DISK)->url($path);
    }

    /**
     * Values for translations: every text setting as ":key", plus ":<phone>_href" and the map links.
     *
     * @return array<string, string>
     */
    public function tokens(Locale $locale): array
    {
        $tokens = [];

        foreach (SettingKey::cases() as $key) {
            if ($key->kind() !== SettingKind::Image) {
                $tokens[$key->value] = $this->text($key, $locale);
            }
        }

        foreach (self::PHONES as $phone) {
            $tokens[$phone->value.'_href'] = $this->tel($phone) ?? '#';
        }

        return $tokens + $this->mapLinks();
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
}
