<?php

namespace App\Models;

use App\Observers\CatalogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Make of the machine a spare part fits: Monforts, Bruckner, Artos… (catalog «Станок» filter). */
#[ObservedBy(CatalogObserver::class)]
class MachineBrand extends Model
{
    protected $fillable = ['slug', 'name', 'sort'];

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
