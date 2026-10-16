<?php

namespace App\Observers;

use App\Services\Site\ContentCache;
use Illuminate\Database\Eloquent\Model;

/** A change to projects, posts or videos renews the cached content pages. */
class ContentObserver
{
    public function __construct(private readonly ContentCache $cache) {}

    public function saved(Model $model): void
    {
        $this->cache->flush();
    }

    public function deleted(Model $model): void
    {
        $this->cache->flush();
    }
}
