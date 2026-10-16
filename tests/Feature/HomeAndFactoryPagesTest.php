<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Client;
use App\Models\Product;
use App\Models\Testimonial;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAndFactoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_products_by_industry_and_catalog_sections(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('"inds":[{"k":"metallurgiya"', false)
            ->assertSee('/product/ir-v01', false)
            ->assertSee('/catalog?cat=valik', false);
    }

    public function test_home_follows_catalog_changes(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->get('/')->assertSee('IR-V01', false);

        Product::firstWhere('sku', 'IR-V01')->update(['is_active' => false]);

        $this->get('/')->assertDontSee('"code":"IR-V01"', false);
    }

    public function test_factory_page_shows_clients_reviews_and_certificates(): void
    {
        Client::create(['name' => 'АГМК']);
        Testimonial::create(['text' => ['ru' => 'Валы служат втрое дольше'], 'author' => 'Иван Петров, главный механик']);
        Certificate::create(['title' => ['ru' => 'ISO 9001'], 'number' => 'UZ-123']);
        Client::create(['name' => 'Скрытый клиент', 'is_published' => false]);

        $this->get('/factory')
            ->assertOk()
            ->assertSee('АГМК', false)
            ->assertSee('Валы служат втрое дольше', false)
            ->assertSee('№ UZ-123', false)
            ->assertDontSee('Скрытый клиент', false);
    }
}
