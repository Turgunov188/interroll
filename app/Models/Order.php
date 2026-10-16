<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Code, PIN and status history are handled by OrderObserver. */
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'lead_id', 'customer_name', 'customer_phone', 'company',
        'status', 'due_date', 'total', 'notes', 'manager_id',
    ];

    // Note: pin and notes are NOT in $hidden on purpose — the admin form needs them.
    // The public tracking page must only output fields it picks explicitly
    // (lookup: App\Actions\Orders\FindOrderForTracking).

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'due_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest('created_at');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
