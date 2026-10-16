<?php

namespace App\Observers;

use App\Models\ProductMedia;
use Illuminate\Support\Facades\Storage;

/** Keeps file metadata in sync and never leaves orphaned files on disk. */
class ProductMediaObserver
{
    /** New files go to the end of their type's list. */
    public function creating(ProductMedia $media): void
    {
        $media->sort ??= 1 + (int) ProductMedia::query()
            ->where('product_id', $media->product_id)
            ->where('type', $media->type)
            ->max('sort');
    }

    public function saving(ProductMedia $media): void
    {
        if (! $media->isDirty('path')) {
            return;
        }

        $disk = Storage::disk($media->disk);

        if ($disk->exists($media->path)) {
            $media->mime_type = $disk->mimeType($media->path) ?: null;
            $media->size = $disk->size($media->path);
        }

        $media->original_name ??= basename($media->path);
    }

    /** A replaced file is deleted once the new one is saved. */
    public function updated(ProductMedia $media): void
    {
        $old = $media->getOriginal('path');

        if ($media->wasChanged('path') && $old && $old !== $media->path) {
            Storage::disk($media->getOriginal('disk') ?: $media->disk)->delete($old);
        }
    }

    public function deleted(ProductMedia $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
    }
}
