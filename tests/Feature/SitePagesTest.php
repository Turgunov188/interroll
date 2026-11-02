<?php

namespace Tests\Feature;

use App\Enums\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_russian_is_served_at_the_root(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('Content-Language', 'ru')
            ->assertSee('<html lang="ru">', false)
            ->assertSee('<link rel="alternate" hreflang="uz" href="'.url('/uz').'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/').'">', false)
            ->assertSee('name="_token"', false) // the price request form posts to /leads
            ->assertSee('action="'.route('leads.store').'"', false);
    }

    public function test_the_menu_marks_the_current_section(): void
    {
        $this->get('/uz/catalog')
            ->assertOk()
            ->assertSee('<a href="/uz/catalog" class="nav__link" aria-current="page">Mahsulotlar</a>', false)
            ->assertSee('<a href="'.url('/en/catalog').'" hreflang="en" lang="en"', false);
    }

    public function test_page_texts_follow_the_language(): void
    {
        $this->get('/en/recoating')->assertOk()->assertSee('Recoating')->assertDontSee('Восстановление покрытия');
        $this->get('/tr/contact')->assertOk()->assertSee('<html lang="tr">', false);
    }

    public function test_every_page_exists_in_every_language(): void
    {
        foreach (Locale::cases() as $locale) {
            foreach (config('site.pages') as $uri => $page) {
                $this->get($locale->path($uri))
                    ->assertOk()
                    ->assertSee('<html lang="'.$locale->value.'">', false);
            }
        }
    }

    public function test_old_uzbek_urls_redirect_permanently_to_english_ones(): void
    {
        $this->get('/katalog')->assertStatus(301)->assertRedirect('/catalog');
        $this->get('/uz/aloqa')->assertStatus(301)->assertRedirect('/uz/contact');
        $this->get('/en/mahsulot/ir-v01')->assertStatus(301)->assertRedirect('/en/product/ir-v01');
        $this->get('/tr/zavod')->assertStatus(301)->assertRedirect('/tr/factory');
    }

    public function test_old_russian_homepage_redirects_to_the_root(): void
    {
        $this->get('/ru')->assertRedirect('/')->assertStatus(301);
    }

    public function test_unknown_language_is_not_found(): void
    {
        $this->get('/de/catalog')->assertNotFound();
    }

    public function test_order_tracking_is_switched_off_for_now(): void
    {
        $this->get('/order-tracking')->assertNotFound();
        $this->get('/')->assertDontSee('/order-tracking', false);
    }
}
