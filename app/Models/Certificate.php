<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Certificate or licence with its scan. */
#[ObservedBy(ContentObserver::class)]
class Certificate extends Model
{
    use HasLocalizedFields, HasPublicFiles;

    protected $fillable = ['title', 'number', 'valid_until', 'file', 'is_published', 'sort'];

    protected function casts(): array
    {
        return ['title' => 'array', 'valid_until' => 'date', 'is_published' => 'boolean'];
    }

    public function publicFileColumns(): array
    {
        return ['file'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }
}
