<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A completed job for the «Работы» page: problem → solution → result, with numbers and photos. */
#[ObservedBy(ContentObserver::class)]
class Project extends Model
{
    use HasLocalizedFields, HasPublicFiles;

    protected $fillable = [
        'slug', 'industry_id', 'title', 'client', 'short', 'problem', 'solution', 'result',
        'kpis', 'cover', 'photo_before', 'photo_after', 'quote', 'quote_author', 'is_published', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'client' => 'array',
            'short' => 'array',
            'problem' => 'array',
            'solution' => 'array',
            'result' => 'array',
            'kpis' => 'array',
            'quote' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function publicFileColumns(): array
    {
        return ['cover', 'photo_before', 'photo_after'];
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderByDesc('id');
    }
}
