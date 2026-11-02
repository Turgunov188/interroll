<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;

/** Database data of a site page; the array becomes the page's view variables (config/site.php "data"). */
interface PageData
{
    /** @return array<string, mixed> */
    public function for(Locale $locale): array;
}
