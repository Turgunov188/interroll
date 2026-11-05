<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Lead;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\Leads\LeadAttachments;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_carry_the_protective_headers(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_a_forged_forwarded_ip_does_not_reset_the_form_limit(): void
    {
        // a visitor straight from the internet (not through the local proxy) makes up X-Forwarded-For
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20']);

        for ($i = 0; $i < AppServiceProvider::LEADS_PER_MINUTE; $i++) {
            $this->postJson('/leads', ['phone' => '+998901234567'], ['X-Forwarded-For' => "203.0.113.{$i}"])->assertCreated();
        }

        $this->postJson('/leads', ['phone' => '+998901234567'], ['X-Forwarded-For' => '198.51.100.7'])->assertTooManyRequests();
    }

    public function test_a_stored_web_page_is_never_linked_in_the_admin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('leads/old.html', '<script>alert(1)</script>');
        Storage::disk('local')->put('leads/val.pdf', '%PDF-1.4');

        $lead = Lead::create(['type' => 'quote', 'status' => 'new', 'name' => 'Тест', 'phone' => '+998901234567', 'attachments' => ['leads/old.html', 'leads/val.pdf']]);
        $files = collect(app(LeadAttachments::class)->for($lead))->keyBy('name');

        $this->assertNull($files['old.html']['url']);
        $this->assertNotNull($files['val.pdf']['url']);
    }

    public function test_the_owner_cannot_demote_or_switch_off_themselves(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'is_active' => true]);
        $this->actingAs($owner);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($owner), ['name' => 'Новое имя', 'role' => UserRole::Sales->value, 'is_active' => false])
            ->assertHasNoActionErrors();

        $owner->refresh();
        $this->assertSame('Новое имя', $owner->name); // the form was saved…
        $this->assertSame(UserRole::Owner, $owner->role); // …without the role and the switch
        $this->assertTrue($owner->is_active);
    }
}
