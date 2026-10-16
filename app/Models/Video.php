<?php

namespace App\Models;

use App\Enums\VideoKind;
use App\Models\Concerns\HasLocalizedFields;
use App\Models\Concerns\HasPublicFiles;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ContentObserver::class)]
class Video extends Model
{
    use HasLocalizedFields, HasPublicFiles;

    protected $fillable = [
        'kind', 'title', 'category', 'description', 'url', 'file', 'poster', 'duration', 'chapters', 'is_published', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'kind' => VideoKind::class,
            'title' => 'array',
            'category' => 'array',
            'description' => 'array',
            'chapters' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function publicFileColumns(): array
    {
        return ['file', 'poster'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    /** YouTube video id from any usual link (watch?v=, youtu.be/, /shorts/, /embed/), or null. */
    public function youtubeId(): ?string
    {
        if (! $this->url || ! preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $this->url, $m)) {
            return null;
        }

        return $m[1];
    }

    /** "3:20" / "1:02:05" → seconds; 0 when empty or malformed. */
    public static function seconds(?string $time): int
    {
        if (! $time || ! preg_match('/^\d{1,2}(:\d{1,2}){0,2}$/', trim($time))) {
            return 0;
        }

        return array_reduce(explode(':', trim($time)), fn (int $sum, string $part): int => $sum * 60 + (int) $part, 0);
    }
}
