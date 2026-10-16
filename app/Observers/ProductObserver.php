<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Media rows are removed by the foreign key cascade, which fires no model events,
     * so files are deleted here first. Soft-deleted products keep their files.
     */
    public function forceDeleting(Product $product): void
    {
        $product->media()->get()->each->delete();
    }
}
