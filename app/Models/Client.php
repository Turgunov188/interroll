<?php

namespace App\Models;

use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Client company shown as a logo on the factory page. */
#[ObservedBy(ContentObserver::class)]
class Client extends Model
{
    use HasPublicFiles;

    protected $fillable = ['name', 'logo', 'is_published', 'sort'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function publicFileColumns(): array
    {
        return ['logo'];
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
