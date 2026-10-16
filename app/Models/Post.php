<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A blog article. Shown when published and its date has come. */
#[ObservedBy(ContentObserver::class)]
class Post extends Model
{
    use HasLocalizedFields, HasPublicFiles;

    protected $fillable = [
        'slug', 'post_category_id', 'title', 'excerpt', 'body', 'author', 'cover', 'cover_caption',
        'read_minutes', 'is_published', 'is_featured', 'published_at', 'seo',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'excerpt' => 'array',
            'body' => 'array',
            'author' => 'array',
            'cover_caption' => 'array',
            'seo' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function publicFileColumns(): array
    {
        return ['cover'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /** Minutes to read: as set in the admin, or about 180 words per minute of the text. */
    public function readMinutes(string $html): int
    {
        if ($this->read_minutes) {
            return $this->read_minutes;
        }

        $words = count(preg_split('/\s+/u', trim(strip_tags($html)), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / 180));
    }
}
