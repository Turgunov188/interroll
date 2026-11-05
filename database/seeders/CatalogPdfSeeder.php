<?php

namespace Database\Seeders;

use App\Actions\Products\AttachProductMedia;
use App\Enums\Configurator;
use App\Enums\Material;
use App\Enums\MediaType;
use App\Models\Category;
use App\Models\Industry;
use App\Models\MachineBrand;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Products from the INTER ROLL / IMPOL catalogue PDF (data/catalog-2026.php): stenter spare parts with
 * their machine makes, polyurethane wheels and seals, with photos cut from the PDF.
 *
 *   php artisan db:seed --class=CatalogPdfSeeder --force
 *
 * Adds only what is missing: rows that already exist (same slug / SKU, also deleted products)
 * are left exactly as they are, so running it again never overwrites edits made in the admin panel.
 */
class CatalogPdfSeeder extends Seeder
{
    private const PHOTOS = __DIR__.'/images/catalog';

    public function run(AttachProductMedia $attach): void
    {
        $data = require __DIR__.'/data/catalog-2026.php';
        $created = 0;
        $skipped = [];

        DB::transaction(function () use ($data, $attach, &$created, &$skipped): void {
            foreach ($data['machines'] as $row) {
                MachineBrand::query()->firstOrCreate(['slug' => $row['slug']], $row);
            }

            foreach ($data['industries'] as $row) {
                Industry::query()->firstOrCreate(['slug' => $row['slug']], $row);
            }

            foreach ($data['categories'] as $row) {
                $parent = $row['parent'] ? Category::query()->where('slug', $row['parent'])->value('id') : null;
                unset($row['parent']);

                Category::query()->firstOrCreate(['slug' => $row['slug']], $row + ['parent_id' => $parent, 'is_active' => true]);
            }

            $categories = Category::query()->pluck('id', 'slug');
            $machines = MachineBrand::query()->pluck('id', 'slug');
            $industries = Industry::query()->pluck('id', 'slug');

            foreach ($data['products'] as $row) {
                if (Product::withTrashed()->where('sku', $row['sku'])->exists()) {
                    continue;
                }

                // a section that existed in the starting catalog may have been renamed or removed in the admin
                if (! isset($categories[$row['category']])) {
                    $skipped[$row['category']][] = $row['sku'];

                    continue;
                }

                $product = Product::query()->create([
                    'sku' => $row['sku'],
                    'slug' => $this->slug($row['sku']),
                    'category_id' => $categories[$row['category']],
                    'machine_brand_id' => isset($row['machine']) ? $machines[$row['machine']] : null,
                    'name' => $row['name'],
                    'short' => $row['short'],
                    'description' => $row['description'] ?? null,
                    'material' => Material::from($row['material']),
                    'configurator' => $row['configurator'] ? Configurator::from($row['configurator']) : null,
                    'made_to_order' => $row['made_to_order'],
                    'price_on_request' => true,
                    'is_active' => true,
                    'sort' => $row['sort'],
                ]);

                $product->industries()->sync(collect($row['industries'])->map(fn (string $slug) => $industries[$slug] ?? null)->filter());
                $attach->handle($product, MediaType::Photo, $this->photos($row['photos'], $row['sku']));
                $created++;
            }
        });

        $missing = array_sum(array_map('count', $skipped));
        $this->command?->info("  {$created} products added, ".(count($data['products']) - $created - $missing).' already there');

        foreach ($skipped as $slug => $skus) {
            $this->command?->warn("  Section «{$slug}» not found, skipped: ".implode(', ', $skus));
        }

        if ($skipped) {
            $this->command?->line('  Sections on this site: '.Category::query()->orderBy('slug')->pluck('slug')->implode(', '));
        }
    }

    /** A free URL slug: «mnf-n25», or «mnf-n25-2» when an admin already used it. */
    private function slug(string $sku): string
    {
        $base = Str::slug($sku);
        $slug = $base;

        for ($i = 2; Product::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    /**
     * Copies the product's photos to the public disk, where admin uploads live too.
     *
     * @param  list<string>  $files
     * @return list<string> paths on the disk
     */
    private function photos(array $files, string $sku): array
    {
        $disk = Storage::disk(MediaType::DISK);

        return collect($files)
            ->filter(fn (string $file) => is_file(self::PHOTOS.'/'.$file))
            ->map(function (string $file) use ($disk, $sku): string {
                $path = MediaType::Photo->directory().'/'.Str::slug($sku).'-'.Str::lower(Str::random(6)).'.webp';
                $disk->put($path, file_get_contents(self::PHOTOS.'/'.$file));

                return $path;
            })
            ->values()
            ->all();
    }
}
