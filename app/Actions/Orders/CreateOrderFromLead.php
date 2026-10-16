<?php

namespace App\Actions\Orders;

use App\Enums\LeadStatus;
use App\Enums\OrderStatus;
use App\Exceptions\LeadAlreadyConverted;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Opens a production order for a lead and marks the lead as won. */
final class CreateOrderFromLead
{
    /** @throws LeadAlreadyConverted */
    public function handle(Lead $lead, ?User $by = null): Order
    {
        if ($lead->order()->exists()) {
            throw LeadAlreadyConverted::for($lead);
        }

        return DB::transaction(function () use ($lead, $by): Order {
            /** @var Order $order */
            $order = $lead->order()->create([
                'customer_name' => $lead->name,
                'customer_phone' => $lead->phone,
                'company' => $lead->company,
                'status' => OrderStatus::Accepted,
                'manager_id' => $lead->manager_id ?? $by?->id,
            ]);

            $lead->update(['status' => LeadStatus::Won]);

            return $order;
        });
    }
}
