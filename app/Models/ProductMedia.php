<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Models\Concerns\HasLocalizedFields;
use App\Observers\CatalogObserver;
use App\Observers\ProductMediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** One file of a product: photo, drawing, 3D model or document. */
#[ObservedBy([ProductMediaObserver::class, CatalogObserver::class])]
class ProductMedia extends Model
{
    use HasLocalizedFields;

    protected $table = 'product_media';

    protected $fillable = ['product_id', 'type', 'disk', 'path', 'original_name', 'mime_type', 'size', 'alt', 'sort'];

    protected $attributes = [
        'disk' => MediaType::DISK,
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'alt' => 'array',
            'size' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeOfType(Builder $query, MediaType $type): void
    {
        $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->original_name ?: $this->path, PATHINFO_EXTENSION));
    }

    /** Can be shown with <img> (drawings may also be PDFs). */
    public function isImage(): bool
    {
        return $this->mime_type
            ? str_starts_with($this->mime_type, 'image/')
            : in_array($this->extension(), ['jpg', 'jpeg', 'png', 'webp', 'svg'], true);
    }

    /** "PDF · 240 KB" */
    public function sizeLabel(): string
    {
        $kb = $this->size ? max(1, (int) round($this->size / 1024)) : null;

        return strtoupper($this->extension()).($kb ? ' · '.($kb >= 1024 ? round($kb / 1024, 1).' MB' : $kb.' KB') : '');
    }
}
