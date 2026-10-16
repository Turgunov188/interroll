<?php

namespace App\Observers;

use App\Services\Catalog\CatalogCache;
use Illuminate\Database\Eloquent\Model;

/** Any change to products, categories or industries refreshes the public catalog. */
class CatalogObserver
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function saved(Model $model): void
    {
        $this->cache->flush();
    }

    public function deleted(Model $model): void
    {
        $this->cache->flush();
    }

    public function restored(Model $model): void
    {
        $this->cache->flush();
    }
}
