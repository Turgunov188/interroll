<?php

namespace App\Console\Commands;

use App\Enums\MediaType;
use App\Models\ProductMedia;
use App\Support\ImageTrim;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/** One-off clean-up: cuts white margins off the product photos already uploaded. Safe to run again. */
final class TrimProductPhotos extends Command
{
    protected $signature = 'products:trim-photos {--dry : only count, change nothing}';

    protected $description = 'Cut white margins off product photos';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->warn('GD has no WebP support here: WebP photos are skipped.');
        }

        $changed = 0;
        $total = 0;

        ProductMedia::query()->where('type', MediaType::Photo)->orderBy('id')->each(function (ProductMedia $media) use (&$changed, &$total): void {
            $disk = Storage::disk($media->disk);
            if (! $disk->exists($media->path)) {
                return;
            }
            $total++;
            $path = $disk->path($media->path);

            if ($this->option('dry')) {
                $image = @imagecreatefromstring((string) file_get_contents($path));
                if ($image && ImageTrim::box($image)) {
                    $changed++;
                    $this->line("  {$media->path}");
                }

                return;
            }

            if (ImageTrim::file($path)) {
                clearstatcache(true, $path);
                $media->forceFill(['size' => filesize($path)])->saveQuietly();
                $changed++;
                $this->line("  {$media->path}");
            }
        });

        $this->info(($this->option('dry') ? 'Would trim' : 'Trimmed')." {$changed} of {$total} photos.");

        return self::SUCCESS;
    }
}
