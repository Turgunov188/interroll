<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateOrderFromLead;
use App\Actions\Orders\FindOrderForTracking;
use App\Enums\LeadStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Exceptions\LeadAlreadyConverted;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFromLeadTest extends TestCase
{
    use RefreshDatabase;

    private function lead(): Lead
    {
        return Lead::create(['name' => 'ООО Тест', 'phone' => '+998901234567', 'company' => 'Тест']);
    }

    public function test_an_order_is_opened_for_a_lead(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Sales]);
        $lead = $this->lead();

        $order = app(CreateOrderFromLead::class)->handle($lead, $manager);

        $this->assertSame('IR-1001', $order->code);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $order->pin);
        $this->assertSame(OrderStatus::Accepted, $order->status);
        $this->assertEquals($manager->id, $order->manager_id);
        $this->assertSame(LeadStatus::Won, $lead->fresh()->status);
        $this->assertSame(1, $order->events()->count());
    }

    public function test_a_lead_cannot_be_converted_twice(): void
    {
        $lead = $this->lead();
        app(CreateOrderFromLead::class)->handle($lead);

        $this->expectException(LeadAlreadyConverted::class);

        app(CreateOrderFromLead::class)->handle($lead);
    }

    public function test_every_stage_change_is_written_to_the_history(): void
    {
        $order = app(CreateOrderFromLead::class)->handle($this->lead());

        $order->update(['status' => OrderStatus::Casting]);
        $order->update(['notes' => 'без смены этапа']);

        $this->assertSame(
            [OrderStatus::Accepted, OrderStatus::Casting],
            $order->events()->reorder('id')->pluck('status')->all(),
        );
    }

    public function test_tracking_needs_both_code_and_pin(): void
    {
        $order = app(CreateOrderFromLead::class)->handle($this->lead());
        $find = app(FindOrderForTracking::class);

        $this->assertTrue($order->is($find->handle(' ir-1001 ', $order->pin)));
        $this->assertNull($find->handle('IR-1001', $order->pin === '0000' ? '1111' : '0000'));
        $this->assertNull($find->handle('IR-9999', $order->pin));
    }
}
