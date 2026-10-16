<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One value of the «Настройки сайта» page. The key is App\Enums\SettingKey,
 * the value is a string, or {"ru": …, "uz": …} for localized keys.
 * Read through App\Services\Site\SiteSettings (cached), not directly.
 */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
