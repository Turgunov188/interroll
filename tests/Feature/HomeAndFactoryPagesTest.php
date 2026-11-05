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

        $response = $this->get('/')->assertOk();

        $industries = $response->viewData('industries');
        $this->assertSame('metallurgiya', $industries[0]->slug);
        $this->assertLessThanOrEqual(3, count($industries[0]->products));
        $this->assertSame('/catalog?cat=valik', $response->viewData('categories')[0]->url);

        $response
            ->assertSee('href="/product/ir-v01"', false)
            ->assertSee('href="/catalog?cat=valik"', false)
            ->assertSee('role="tabpanel" id="panel-metallurgiya"', false);
    }

    public function test_home_follows_catalog_changes(): void
    {
        $this->seed(CatalogSeeder::class);
        $codes = fn () => collect($this->get('/')->viewData('industries'))->flatMap->products->pluck('code')->all();
        $this->assertContains('IR-V01', $codes());

        Product::firstWhere('sku', 'IR-V01')->update(['is_active' => false]);

        $this->assertNotContains('IR-V01', $codes());
        $this->get('/')->assertDontSee('href="/product/ir-v01"', false);
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

    public function test_factory_page_tells_about_impol_group_in_every_language(): void
    {
        $this->get('/factory')
            ->assertOk()
            ->assertSee('Тот же опыт, то же качество — новая сила')
            ->assertSee('Видение')
            ->assertSee('Румыния')
            ->assertSee('/images/factory/impol-plant.webp', false);

        $this->get('/uz/factory')->assertOk()->assertSee('O&#039;sha tajriba', false);
        $this->get('/en/factory')->assertOk()->assertSee('IMPOL GROUP supplies');
        $this->get('/tr/factory')->assertOk()->assertSee('Aynı tecrübe, aynı kalite');
    }

    public function test_featured_products_lead_their_industry_tab(): void
    {
        $this->seed(CatalogSeeder::class);
        Product::query()->update(['is_featured' => false]);
        $industry = \App\Models\Industry::query()->whereHas('products', fn ($q) => $q->published())->ordered()->first();
        $last = $industry->products()->published()->ordered()->get()->last();
        $last->update(['is_featured' => true]);
        $shown = [];

        for ($i = 0; $i < 5; $i++) {
            $tab = collect($this->get('/')->viewData('industries'))->firstWhere('slug', $industry->slug);
            $this->assertSame($last->sku, $tab->products[0]->code);
            $this->assertCount(min(3, $industry->products()->published()->count()), $tab->products);
            $shown = [...$shown, ...array_column(array_slice($tab->products, 1), 'code')];
        }

        $this->assertNotContains($last->sku, $shown, 'a featured product is never repeated in the random slots');
    }
}
