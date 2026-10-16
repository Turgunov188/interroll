<?php

namespace App\Actions\Products;

use App\Enums\MediaType;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Adds already-uploaded files (paths on the media disk) to a product, after its existing ones. */
final class AttachProductMedia
{
    /**
     * @param  list<string>  $paths
     * @param  array<string, string>  $originalNames  path => file name as uploaded
     * @return Collection<int, ProductMedia>
     */
    public function handle(Product $product, MediaType $type, array $paths, array $originalNames = []): Collection
    {
        return DB::transaction(function () use ($product, $type, $paths, $originalNames): Collection {
            $sort = (int) $product->media()->where('type', $type)->max('sort');
            $created = collect();

            // A plain loop on purpose: an arrow fn would capture $sort by value and number every file the same.
            foreach (array_values($paths) as $path) {
                $created->push($product->media()->create([
                    'type' => $type,
                    'path' => $path,
                    'original_name' => $originalNames[$path] ?? null,
                    'sort' => ++$sort,
                ]));
            }

            return $created;
        });
    }
}
