<?php

namespace App\Actions\Orders;

use App\Models\Order;

/** Public tracking page: an order is shown only when both the code and the PIN match. */
final class FindOrderForTracking
{
    public function handle(string $code, string $pin): ?Order
    {
        $order = Order::query()->where('code', strtoupper(trim($code)))->first();

        if ($order === null || ! hash_equals($order->pin, trim($pin))) {
            return null;
        }

        return $order;
    }
}
