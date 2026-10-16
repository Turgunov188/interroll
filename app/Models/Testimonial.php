<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Customer review («Нам доверяют»). */
#[ObservedBy(ContentObserver::class)]
class Testimonial extends Model
{
    use HasLocalizedFields, HasPublicFiles;

    protected $fillable = ['text', 'author', 'photo', 'is_published', 'sort'];

    protected function casts(): array
    {
        return ['text' => 'array', 'is_published' => 'boolean'];
    }

    public function publicFileColumns(): array
    {
        return ['photo'];
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
