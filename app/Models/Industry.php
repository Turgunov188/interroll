<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Observers\CatalogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(CatalogObserver::class)]
class Industry extends Model
{
    use HasLocalizedFields;

    protected $fillable = ['slug', 'name', 'icon', 'sort'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
