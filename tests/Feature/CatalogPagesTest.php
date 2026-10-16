<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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

    /** @return array<string, mixed> window.__IR of a rendered page */
    private function pageData(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="ir-data">(.*?)</script>#s', $html, $m));

        return json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_catalog_page_lists_published_products_from_the_database(): void
    {
        $data = $this->pageData($this->get('/catalog')->assertOk()->getContent());

        $this->assertCount(18, $data['products']);
        $this->assertSame(['valik', 'Валы'], $data['cats'][0]);
        $this->assertSame([
            'cat' => 'valik',
            'code' => 'IR-V01',
            'name' => 'Полиуретановый вал',
            'mat' => 'pu',
            'matL' => 'Полиуретан',
            'shore' => '90 A',
            'spec' => 'С металлическим сердечником, под размер заказа · Ø [__]–[___] мм',
            'url' => '/product/ir-v01',
            'img' => null,
            'inds' => ['metallurgiya', 'poligrafiya'],
        ], $data['products'][0]);
    }

    public function test_industry_filter_lists_only_industries_with_products(): void
    {
        $data = $this->pageData($this->get('/catalog')->getContent());

        $this->assertSame(['metallurgiya', 'Металлургия'], $data['industries'][0]);
        $this->assertCount(5, $data['industries']);

        Product::whereIn('sku', ['IR-A01', 'IR-A02', 'IR-M02', 'IR-S01'])->get()->each->update(['is_active' => false]);

        $slugs = array_column($this->pageData($this->get('/catalog')->getContent())['industries'], 0);
        $this->assertNotContains('avtomobil', $slugs);
    }

    public function test_product_page_lists_its_industries(): void
    {
        $uses = $this->pageData($this->get('/uz/product/ir-v01')->getContent())['product']['uses'];

        $this->assertSame(['Metallurgiya', 'Poligrafiya'], array_column($uses, 't'));
    }

    public function test_catalog_is_translated_and_links_stay_in_the_language(): void
    {
        $data = $this->pageData($this->get('/uz/catalog')->assertOk()->getContent());

        $this->assertSame('Poliuretan valik', $data['products'][0]['name']);
        $this->assertSame('/uz/product/ir-v01', $data['products'][0]['url']);
    }

    public function test_hidden_products_and_categories_are_not_shown(): void
    {
        Product::where('sku', 'IR-V02')->update(['is_active' => false]);
        Category::where('slug', 'avto')->first()->update(['is_active' => false]);

        $data = $this->pageData($this->get('/catalog')->getContent());
        $codes = array_column($data['products'], 'code');

        $this->assertNotContains('IR-V02', $codes);
        $this->assertNotContains('IR-A01', $codes);
        $this->assertNotContains('avto', array_column($data['cats'], 0));
    }

    public function test_admin_changes_reach_the_cached_catalog(): void
    {
        $this->get('/catalog'); // warm the cache

        Product::where('sku', 'IR-V01')->first()->update(['name' => ['ru' => 'Новое имя', 'uz' => 'Yangi nom']]);

        $data = $this->pageData($this->get('/catalog')->getContent());
        $this->assertSame('Новое имя', $data['products'][0]['name']);
    }

    public function test_product_page_shows_the_product(): void
    {
        $response = $this->get('/product/ir-k01')->assertOk();
        $data = $this->pageData($response->getContent());

        $this->assertSame('IR-K01', $data['product']['code']);
        $this->assertSame('Восстановление покрытия вала', $data['product']['name']);
        $this->assertCount(3, $data['related']);
        $this->assertSame('/product/ir-k02', $data['related'][0]['url']); // same category first
        $response->assertSee('href="'.url('/uz/product/ir-k01').'"', false); // hreflang (base URL comes from APP_URL)
        $response->assertDontSee('__SUFFIX__', false);
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
