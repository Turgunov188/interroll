<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;

/** Server data for a site page; it reaches the page as window.__IR (see config/site.php "data"). */
interface PageData
{
    /** @return array<string, mixed> */
    public function for(Locale $locale): array;
}
