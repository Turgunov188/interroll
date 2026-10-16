<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\Locale;
use App\Models\Lead;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_site_form_creates_a_new_lead(): void
    {
        $response = $this->postJson('/leads', [
            'name' => 'Алишер',
            'phone' => '+998 90 123 45 67',
            'type' => 'recoating',
            'locale' => 'uz',
            'payload' => json_encode(['qty' => 4]),
        ]);

        $response->assertCreated()->assertJson(['ok' => true, 'number' => 'L-000001']);

        $lead = Lead::sole();
        $this->assertSame(LeadType::Recoating, $lead->type);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame(Locale::Uz, $lead->locale);
        $this->assertSame(['qty' => 4], $lead->payload);
    }

    public function test_defaults_are_used_when_the_form_sends_only_a_phone(): void
    {
        $this->postJson('/leads', ['phone' => '+998901234567'])->assertCreated();

        $lead = Lead::sole();
        $this->assertSame('С сайта', $lead->name);
        $this->assertSame(LeadType::Quote, $lead->type);
        $this->assertSame(Locale::main(), $lead->locale);
    }

    public function test_phone_is_required(): void
    {
        $this->postJson('/leads', ['name' => 'Без телефона'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_unknown_type_and_locale_are_rejected(): void
    {
        $this->postJson('/leads', ['phone' => '+998901234567', 'type' => 'spam', 'locale' => 'de'])
            ->assertJsonValidationErrors(['type', 'locale']);
    }

    public function test_bots_filling_the_honeypot_get_a_fake_success(): void
    {
        $this->postJson('/leads', ['phone' => '+998901234567', 'website' => 'http://spam.example'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_drawings_are_stored_privately(): void
    {
        Storage::fake('local');

        $this->post('/leads', [
            'phone' => '+998901234567',
            'attachments' => [UploadedFile::fake()->create('val.pdf', 200, 'application/pdf')],
        ], ['Accept' => 'application/json'])->assertCreated();

        $path = Lead::sole()->attachments[0];
        $this->assertStringStartsWith('leads/', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_executable_attachments_are_rejected(): void
    {
        Storage::fake('local');

        $this->post('/leads', [
            'phone' => '+998901234567',
            'attachments' => [UploadedFile::fake()->create('virus.exe', 10)],
        ], ['Accept' => 'application/json'])->assertJsonValidationErrors('attachments.0');
    }

    public function test_the_product_page_sends_the_chosen_product_and_sizes(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->postJson('/leads', [
            'phone' => '+998901234567',
            'items' => json_encode([[
                'code' => 'IR-G01',
                'qty' => 6,
                'options' => ['mat' => 'pu', 'shore' => 92, 'D' => 150, 'd' => 40, 'B' => 50, 'T' => 15, 'evil' => '<script>'],
            ]]),
        ])->assertCreated();

        $item = Lead::sole()->items()->sole();

        $this->assertSame('IR-G01', $item->product->sku);
        $this->assertSame('Полиуретановое колесо', $item->product_name);
        $this->assertSame(6, $item->quantity);
        $this->assertSame([
            'Материал' => 'Полиуретан',
            'Твёрдость' => '92 Shore A',
            'Наружный диаметр D' => '150 mm',
            'Внутренний диаметр d' => '40 mm',
            'Ширина B' => '50 mm',
            'Толщина полиуретана T' => '15 mm',
        ], $item->options); // unknown keys are dropped
    }

    public function test_the_catalog_cart_sends_several_parts(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->postJson('/leads', [
            'phone' => '+998901234567',
            'items' => json_encode([['code' => 'IR-V01', 'qty' => 2], ['code' => 'IR-P01', 'qty' => 1], ['code' => 'NO-SUCH', 'qty' => 3]]),
        ])->assertCreated();

        $items = Lead::sole()->items()->orderBy('id')->get();

        $this->assertSame(['Полиуретановый вал', 'Скребковая пластина', 'NO-SUCH'], $items->pluck('product_name')->all());
        $this->assertNull($items[2]->product_id); // unknown code is kept as text for the manager
    }

    public function test_broken_items_do_not_lose_the_request(): void
    {
        $this->postJson('/leads', [
            'phone' => '+998901234567',
            'items' => json_encode(['oops', ['qty' => 2], ['code' => str_repeat('x', 200)]]),
        ])->assertCreated();

        $this->assertSame(0, Lead::sole()->items()->count());
    }

    public function test_the_recoating_form_sends_typed_sizes_as_an_item(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->postJson('/leads', [
            'phone' => '+998901234567',
            'type' => 'recoating',
            'items' => json_encode([['code' => 'IR-K01', 'qty' => 6, 'options' => ['mat' => 'nbr', 'D' => '120', 'L' => '800 мм']]]),
        ])->assertCreated();

        $lead = Lead::sole();
        $item = $lead->items()->sole();

        $this->assertNull($lead->message); // sizes no longer end up in the comment
        $this->assertSame(6, $item->quantity);
        $this->assertSame(['Материал' => 'Резина NBR', 'Диаметр' => '120 mm', 'Рабочая длина' => '800 mm'], $item->options);
    }
}
