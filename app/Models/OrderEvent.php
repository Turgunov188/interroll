<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Observers\OrderEventObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(OrderEventObserver::class)]
class OrderEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['order_id', 'status', 'comment', 'is_public', 'user_id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'is_public' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
