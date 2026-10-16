<?php

namespace App\Actions\Leads;

use App\Data\LeadItemData;
use App\Enums\Locale;
use App\Models\Lead;
use App\Models\LeadItem;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Turns the parts a customer sent (product page, catalog cart) into lead items the
 * manager can read: product link, Russian name, quantity and the chosen sizes.
 */
final class AddLeadItems
{
    /**
     * @param  list<LeadItemData>  $items
     * @return Collection<int, LeadItem>
     */
    public function handle(Lead $lead, array $items): Collection
    {
        if ($items === []) {
            return collect();
        }

        $products = Product::withTrashed()
            ->whereIn('sku', array_map(fn (LeadItemData $item): string => $item->code, $items))
            ->get()
            ->keyBy('sku');

        return collect($items)->map(function (LeadItemData $item) use ($lead, $products): LeadItem {
            /** @var Product|null $product */
            $product = $products->get($item->code);

            return $lead->items()->create([
                'product_id' => $product?->getKey(),
                'product_name' => $product?->tr('name', Locale::Ru) ?: $item->code,
                'quantity' => $item->quantity,
                'options' => $product?->configurator?->describeSelection($item->options) ?: null,
            ]);
        });
    }
}
