<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Observers\CatalogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CatalogObserver::class)]
class Category extends Model
{
    use HasLocalizedFields;

    protected $fillable = ['parent_id', 'slug', 'name', 'description', 'image', 'sort', 'is_active', 'seo'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'seo' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
