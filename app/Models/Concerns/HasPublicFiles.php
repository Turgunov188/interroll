<?php

namespace App\Models\Concerns;

use App\Enums\MediaType;
use Illuminate\Support\Facades\Storage;

/** Image columns that hold a path on the public disk (uploaded in the admin). */
trait HasPublicFiles
{
    public function fileUrl(string $column): ?string
    {
        $path = $this->getAttribute($column);

        return filled($path) ? Storage::disk(MediaType::DISK)->url($path) : null;
    }

    /** Files of the listed columns are removed from the disk when replaced or when the record is deleted. */
    protected static function bootHasPublicFiles(): void
    {
        static::updated(function (self $model): void {
            foreach ($model->publicFileColumns() as $column) {
                $old = $model->getOriginal($column);
                if ($model->wasChanged($column) && filled($old)) {
                    Storage::disk(MediaType::DISK)->delete($old);
                }
            }
        });

        static::deleted(function (self $model): void {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }
            foreach ($model->publicFileColumns() as $column) {
                if (filled($model->getAttribute($column))) {
                    Storage::disk(MediaType::DISK)->delete($model->getAttribute($column));
                }
            }
        });
    }

    /** @return list<string> */
    abstract public function publicFileColumns(): array;
}
