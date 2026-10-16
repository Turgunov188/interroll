<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Blog filter: «Выбор», «Материалы», «Эксплуатация»… */
#[ObservedBy(ContentObserver::class)]
class PostCategory extends Model
{
    use HasLocalizedFields;

    protected $fillable = ['slug', 'name', 'sort'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }
}
