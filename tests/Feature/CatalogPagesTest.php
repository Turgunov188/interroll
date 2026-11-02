<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\Site\ViewModels\Option;
use App\Services\Site\ViewModels\ProductCard;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);
    }

    public function test_catalog_page_lists_published_products_from_the_database(): void
    {
        $response = $this->get('/catalog')->assertOk();

        /** @var list<ProductCard> $products */
        $products = $response->viewData('products');
        $this->assertCount(18, $products);
        $this->assertEquals(new Option('valik', 'Валы'), $response->viewData('categories')[0]);

        $first = $products[0];
        $this->assertSame('IR-V01', $first->code);
        $this->assertSame('Полиуретановый вал', $first->name);
        $this->assertSame('/product/ir-v01', $first->url);
        $this->assertSame('pu', $first->material);
        $this->assertSame('Полиуретан', $first->materialLabel);
        $this->assertSame('90 A', $first->hardness);
        $this->assertSame([90, 90], [$first->hardnessMin, $first->hardnessMax]);
        $this->assertSame(['metallurgiya', 'poligrafiya'], $first->industries);
        $this->assertNull($first->image);

        // cards are rendered on the server, the script only filters them
        $response->assertSee('Полиуретановый вал')->assertSee('href="/product/ir-v01"', false);
    }

    public function test_industry_filter_lists_only_industries_with_products(): void
    {
        $industries = $this->get('/catalog')->viewData('industries');

        $this->assertEquals(new Option('metallurgiya', 'Металлургия'), $industries[0]);
        $this->assertCount(5, $industries);

        Product::whereIn('sku', ['IR-A01', 'IR-A02', 'IR-M02', 'IR-S01'])->get()->each->update(['is_active' => false]);

        $slugs = array_column($this->get('/catalog')->viewData('industries'), 'value');
        $this->assertNotContains('avtomobil', $slugs);
    }

    public function test_product_page_lists_its_industries(): void
    {
        $product = $this->get('/uz/product/ir-v01')->viewData('product');

        $this->assertSame(['Metallurgiya', 'Poligrafiya'], array_column($product->applications, 'name'));
    }

    public function test_catalog_is_translated_and_links_stay_in_the_language(): void
    {
        $response = $this->get('/uz/catalog')->assertOk();
        $first = $response->viewData('products')[0];

        $this->assertSame('Poliuretan valik', $first->name);
        $this->assertSame('/uz/product/ir-v01', $first->url);
        $response->assertSee('Mahsulotlar')->assertDontSee('Продукция');
    }

    public function test_hidden_products_and_categories_are_not_shown(): void
    {
        Product::where('sku', 'IR-V02')->update(['is_active' => false]);
        Category::where('slug', 'avto')->first()->update(['is_active' => false]);

        $response = $this->get('/catalog');
        $codes = array_column($response->viewData('products'), 'code');

        $this->assertNotContains('IR-V02', $codes);
        $this->assertNotContains('IR-A01', $codes);
        $this->assertNotContains('avto', array_column($response->viewData('categories'), 'value'));
    }

    public function test_admin_changes_reach_the_cached_catalog(): void
    {
        $this->get('/catalog'); // warm the cache

        Product::where('sku', 'IR-V01')->first()->update(['name' => ['ru' => 'Новое имя', 'uz' => 'Yangi nom']]);

        $this->assertSame('Новое имя', $this->get('/catalog')->viewData('products')[0]->name);
    }

    public function test_product_page_shows_the_product(): void
    {
        $response = $this->get('/product/ir-k01')->assertOk();

        $this->assertSame('IR-K01', $response->viewData('product')->code);
        $this->assertSame('Восстановление покрытия вала', $response->viewData('product')->name);
        $this->assertCount(3, $response->viewData('related'));
        $this->assertSame('/product/ir-k02', $response->viewData('related')[0]->url); // same category first

        $response
            ->assertSee('<title>Восстановление покрытия вала — Inter Roll</title>', false)
            ->assertSee('hreflang="uz" href="'.url('/uz/product/ir-k01').'"', false) // base URL comes from APP_URL
            ->assertSee('<link rel="canonical" href="'.url('/product/ir-k01').'">', false);
    }

    public function test_unknown_or_hidden_product_is_not_found(): void
    {
        $this->get('/product/nope')->assertNotFound();

        Product::where('sku', 'IR-V01')->update(['is_active' => false]);
        $this->get('/product/ir-v01')->assertNotFound();
    }

    public function test_plain_product_url_opens_the_default_product(): void
    {
        $this->get('/product')->assertRedirect('/product/ir-v01');
        $this->get('/en/product')->assertRedirect('/en/product/ir-v01');
    }
}
