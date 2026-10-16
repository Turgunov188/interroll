<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadItem extends Model
{
    protected $fillable = ['lead_id', 'product_id', 'product_name', 'quantity', 'options'];

    protected function casts(): array
    {
        return ['options' => 'array', 'quantity' => 'integer'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
