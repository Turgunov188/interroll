<?php

namespace App\Observers;

use App\Models\OrderEvent;

class OrderEventObserver
{
    /** Who made the change (null when it came from the system or the public site). */
    public function creating(OrderEvent $event): void
    {
        $event->user_id ??= auth()->id();
    }
}
