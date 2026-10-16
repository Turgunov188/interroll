<?php

namespace App\Models;

use App\Enums\Configurator;
use App\Enums\Material;
use App\Enums\MediaType;
use App\Models\Concerns\HasLocalizedFields;
use App\Observers\CatalogObserver;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProductObserver::class, CatalogObserver::class])]
class Product extends Model
{
    use HasLocalizedFields, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'slug', 'name', 'short', 'description',
        'material', 'hardness', 'hardness_max', 'hardness_scale', 'specs',
        'price', 'price_on_request', 'made_to_order', 'configurator',
        'is_featured', 'is_active', 'sort', 'seo',
    ];

    protected function casts(): array
    {
        return [
            'material' => Material::class,
            'name' => 'array',
            'short' => 'array',
            'description' => 'array',
            'specs' => 'array',
            'seo' => 'array',
            'price' => 'decimal:2',
            'price_on_request' => 'boolean',
            'made_to_order' => 'boolean',
            'configurator' => Configurator::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class)->orderBy('industries.sort')->orderBy('industries.id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Visible on the site: active product in an active category. */
    public function scopePublished(Builder $query): void
    {
        $query->active()->whereHas('category', fn (Builder $q) => $q->where('is_active', true));
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('sku');
    }

    /** "90 A" or "70–95 A" */
    public function hardnessRange(): ?string
    {
        if (! $this->hardness) {
            return null;
        }

        $range = $this->hardness_max && $this->hardness_max !== $this->hardness
            ? $this->hardness.'–'.$this->hardness_max
            : (string) $this->hardness;

        return $range.' '.($this->hardness_scale ?: 'A');
    }

    /** "90 Shore A" / "70–95 Shore A" */
    public function hardnessLabel(): ?string
    {
        $range = $this->hardnessRange();

        return $range ? preg_replace('/ (\w+)$/', ' Shore $1', $range) : null;
    }

    /** All files, in the order set in the admin panel. */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort')->orderBy('id');
    }

    /** First photo: the catalog card picture. */
    public function mainPhoto(): HasOne
    {
        return $this->hasOne(ProductMedia::class)->ofMany(
            ['sort' => 'min', 'id' => 'min'],
            fn (Builder $query) => $query->where('type', MediaType::Photo),
        );
    }

    /** Path of the main photo on the public disk. */
    public function mainImage(): ?string
    {
        return $this->mainPhoto?->path;
    }
}
