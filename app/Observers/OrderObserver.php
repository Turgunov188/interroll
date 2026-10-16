<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public const CODE_OFFSET = 1000;

    /** The customer gets the code and this PIN to open the tracking page. */
    public function creating(Order $order): void
    {
        $order->pin ??= str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /** Public tracking code (IR-1001, IR-1002…) and the first history row. */
    public function created(Order $order): void
    {
        $order->forceFill(['code' => 'IR-'.(self::CODE_OFFSET + $order->getKey())])->saveQuietly();

        $order->events()->create(['status' => $order->status]);
    }

    /** Every stage change is written to the history shown to the customer. */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $order->events()->create(['status' => $order->status]);
        }
    }
}
