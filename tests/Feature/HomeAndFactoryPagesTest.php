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

        $first = $industries[0]->products[0]->code;
        $response
            ->assertSee('href="/product/'.strtolower($first).'"', false)
            ->assertSee('href="/catalog?cat=valik"', false)
            ->assertSee('role="tabpanel" id="panel-metallurgiya"', false);
    }

    public function test_home_follows_catalog_changes(): void
    {
        $this->seed(CatalogSeeder::class);
        $codes = fn () => collect($this->get('/')->viewData('industries'))->flatMap->products->pluck('code')->all();
        $codes(); // warm the cache

        Product::firstWhere('sku', 'IR-V01')->update(['is_active' => false]);

        for ($i = 0; $i < 10; $i++) {
            $this->assertNotContains('IR-V01', $codes());
        }
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

    public function test_industry_tabs_show_random_products_of_that_industry(): void
    {
        $this->seed(CatalogSeeder::class);
        $industry = \App\Models\Industry::query()->whereHas('products', fn ($q) => $q->published())->ordered()->first();
        $own = $industry->products()->published()->pluck('sku')->all();

        $tab = collect($this->get('/')->viewData('industries'))->firstWhere('slug', $industry->slug);

        $this->assertCount(min(3, count($own)), $tab->products);
        $codes = array_column($tab->products, 'code');
        $this->assertSame($codes, array_unique($codes));
        $this->assertEmpty(array_diff($codes, $own));
    }

    public function test_a_short_industry_row_is_completed_by_the_catalog_card(): void
    {
        $this->seed(CatalogSeeder::class);
        $industry = \App\Models\Industry::query()->whereHas('products', fn ($q) => $q->published())->ordered()->first();
        $industry->products()->published()->ordered()->get()->slice(1)->each->update(['is_active' => false]);

        $response = $this->get('/');
        $tab = collect($response->viewData('industries'))->firstWhere('slug', $industry->slug);

        $this->assertCount(1, $tab->products);
        $this->assertSame(2, $tab->missing());
        $response->assertSee('href="/catalog?industry='.$industry->slug.'"', false)
            ->assertSee('--span: 2', false);
    }
}
