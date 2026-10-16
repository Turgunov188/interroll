<?php

namespace App\Observers;

use App\Models\Lead;

class LeadObserver
{
    /** Human-friendly number once the id exists: L-000123. */
    public function created(Lead $lead): void
    {
        $lead->forceFill([
            'number' => 'L-'.str_pad((string) $lead->getKey(), 6, '0', STR_PAD_LEFT),
        ])->saveQuietly();
    }
}
