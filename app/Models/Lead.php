<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\Locale;
use App\Observers\LeadObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model
{
    protected $fillable = [
        'type', 'status', 'name', 'phone', 'company', 'email', 'message', 'notes',
        'attachments', 'payload', 'locale', 'source', 'manager_id', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeadType::class,
            'status' => LeadStatus::class,
            'locale' => Locale::class,
            'attachments' => 'array',
            'payload' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LeadItem::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
