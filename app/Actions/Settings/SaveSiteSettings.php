<?php

namespace App\Actions\Settings;

use App\Enums\MediaType;
use App\Enums\SettingKey;
use App\Enums\SettingKind;
use App\Services\Site\SiteSettings;
use Illuminate\Support\Facades\Storage;

/** Saves the «Настройки сайта» form; a replaced or removed photo is deleted from the disk. */
final class SaveSiteSettings
{
    public function __construct(private readonly SiteSettings $settings) {}

    /** @param  array<string, mixed>  $values */
    public function handle(array $values): void
    {
        $before = $this->settings->all();

        $this->settings->save($values);

        foreach (SettingKey::cases() as $key) {
            if ($key->kind() !== SettingKind::Image || ! array_key_exists($key->value, $values)) {
                continue;
            }

            $old = $before[$key->value] ?? null;

            if (is_string($old) && $old !== '' && $old !== ($values[$key->value] ?? null)) {
                Storage::disk(MediaType::DISK)->delete($old);
            }
        }
    }
}
