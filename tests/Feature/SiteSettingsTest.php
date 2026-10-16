<?php

namespace Tests\Feature;

use App\Enums\Locale;
use App\Enums\SettingKey;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Site\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function settings(): SiteSettings
    {
        return app(SiteSettings::class);
    }

    public function test_contacts_from_the_admin_appear_on_the_site(): void
    {
        $this->settings()->save([
            SettingKey::Phone->value => '+998 71 200-00-00',
            SettingKey::Telegram->value => 'interroll',
            SettingKey::ReplyMinutes->value => '15',
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('+998 71 200-00-00', false)
            ->assertSee('tel:+998712000000', false)
            ->assertSee('https://t.me/interroll', false)
            ->assertSee('15', false)
            ->assertDontSee('__S_', false);
    }

    public function test_a_value_not_filled_yet_shows_a_visible_gap(): void
    {
        $this->get('/catalog')->assertOk()->assertSee('[__]', false)->assertDontSee('__S_lead_days__', false);
    }

    public function test_localized_values_follow_the_page_language_and_fall_back_to_russian(): void
    {
        $this->settings()->save([SettingKey::Address->value => ['ru' => 'Ташкент, ул. Заводская, 1', 'uz' => 'Toshkent, Zavod ko‘chasi, 1']]);

        $this->assertSame('Toshkent, Zavod ko‘chasi, 1', $this->settings()->get(SettingKey::Address, Locale::Uz));
        $this->assertSame('Ташкент, ул. Заводская, 1', $this->settings()->get(SettingKey::Address, Locale::En));
    }

    public function test_quotes_cannot_break_the_page_script(): void
    {
        $this->settings()->save([SettingKey::Address->value => ['ru' => "Ko'cha \"A\" </script>"]]);

        $js = $this->settings()->placeholders(Locale::Ru)['__SJ_address__'];

        $this->assertStringNotContainsString("'", $js);
        $this->assertStringNotContainsString('"', $js);
        $this->assertStringNotContainsString('</script>', $js);
    }

    public function test_map_links_are_built_from_the_coordinates(): void
    {
        $this->assertSame('#', $this->settings()->mapLinks()['map_google']);

        $this->settings()->save([SettingKey::Coordinates->value => '41.311081, 69.240562']);

        $this->assertSame('https://www.google.com/maps/search/?api=1&query=41.311081,69.240562', $this->settings()->mapLinks()['map_google']);
    }

    public function test_only_admins_open_the_settings_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]))->get('/admin/site-settings')->assertOk();
        $this->actingAs(User::factory()->create(['role' => UserRole::Sales, 'is_active' => true]))->get('/admin/site-settings')->assertForbidden();
    }
}
