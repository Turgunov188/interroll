<?php

namespace Tests\Feature;

use App\Enums\Material;
use App\Enums\SettingKey;
use App\Models\Category;
use App\Models\MachineBrand;
use App\Models\Product;
use App\Services\Site\SiteSettings;
use App\Services\Site\ViewModels\Option;
use App\Services\Site\ViewModels\ProductCard;
use Database\Seeders\CatalogPdfSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Stenter spare parts from the catalogue PDF: machine makes, part types, photos and the catalog filters. */
class CatalogPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([CatalogSeeder::class, CatalogPdfSeeder::class]);
    }

    public function test_parts_are_added_with_their_machine_type_and_photo(): void
    {
        $clip = Product::query()->where('sku', 'MNF001')->with(['category.parent', 'machineBrand', 'media'])->sole();

        $this->assertSame('Клупп Monforts стандартный', $clip->name['ru']);
        $this->assertSame('Monforts clip, standard', $clip->name['en']);
        $this->assertSame('Monforts', $clip->machineBrand->name);
        $this->assertSame('stenter-clips', $clip->category->slug);
        $this->assertSame('stenter', $clip->category->parent->slug);
        $this->assertSame(Material::Other, $clip->material);
        $this->assertSame(['tekstil'], $clip->industries()->pluck('slug')->all());

        $photo = $clip->media->sole();
        Storage::disk('public')->assertExists($photo->path);
        $this->assertSame('image/webp', $photo->mime_type);

        $this->assertSame(19, MachineBrand::query()->count());
        $this->assertSame(Material::Silicone, Product::query()->where('sku', 'IR-U01')->value('material'));
    }

    public function test_running_again_adds_nothing_and_keeps_admin_edits(): void
    {
        $count = Product::withTrashed()->count();
        Product::query()->where('sku', 'MNF001')->sole()->update(['name' => ['ru' => 'Изменено в админке']]);
        Product::query()->where('sku', 'BRC001')->sole()->delete();
        Category::query()->where('slug', 'stenter-clips')->update(['sort' => 99]);

        $this->seed(CatalogPdfSeeder::class);

        $this->assertSame($count, Product::withTrashed()->count());
        $this->assertSame('Изменено в админке', Product::query()->where('sku', 'MNF001')->value('name')['ru']);
        $this->assertNull(Product::query()->where('sku', 'BRC001')->first());
        $this->assertSame(99, Category::query()->where('slug', 'stenter-clips')->value('sort'));
    }

    public function test_catalog_shows_sections_as_tabs_and_part_types_inside_them(): void
    {
        $response = $this->get('/catalog')->assertOk();

        $tabs = array_column($response->viewData('categories'), 'value');
        $this->assertContains('stenter', $tabs);
        $this->assertContains('seals', $tabs);
        $this->assertNotContains('stenter-clips', $tabs);

        $this->assertEquals(new Option('stenter-clips', 'Клуппы'), $response->viewData('types')['stenter'][0]);
        $this->assertEquals(new Option('monforts', 'Monforts'), $response->viewData('machines')[0]);
        $this->assertTrue($response->viewData('silicone'));

        /** @var ProductCard $card */
        $card = collect($response->viewData('products'))->firstWhere('code', 'MNF001');
        $this->assertSame(['stenter', 'stenter-clips', 'monforts'], [$card->category, $card->type, $card->machine]);
        $this->assertStringContainsString('monforts', $card->forScript()['text']);

        $wheel = collect($response->viewData('products'))->firstWhere('code', 'IR-G01');
        $this->assertSame(['gildirak', '', ''], [$wheel->category, $wheel->type, $wheel->machine]);

        $response->assertSee('Все станки')->assertSee('Тип детали');
    }

    public function test_product_page_names_the_machine_and_skips_polyurethane_notes(): void
    {
        $this->get('/product/mnf001')
            ->assertOk()
            ->assertSee('Подходит для станка')
            ->assertSee('Monforts')
            ->assertDontSee('Рабочая температура')
            ->assertDontSee('Почему полиуретан?');

        $this->get('/product/ir-v01')->assertSee('Рабочая температура')->assertSee('Почему полиуретан?');
    }

    public function test_every_language_has_the_names(): void
    {
        $this->get('/uz/catalog')->assertOk()->assertSee('Monforts klips standart');
        $this->get('/en/catalog')->assertOk()->assertSee('Monforts clip, standard');
        $this->get('/tr/catalog')->assertOk()->assertSee('Monforts klips standart')->assertSee('Tüm makineler');
    }

    public function test_parts_of_a_missing_section_are_skipped_and_the_rest_is_added(): void
    {
        Product::query()->forceDelete();
        Category::query()->where('slug', 'gildirak')->delete();

        $this->seed(CatalogPdfSeeder::class);

        $this->assertNull(Product::query()->where('sku', 'IR-G03')->first());
        $this->assertNotNull(Product::query()->where('sku', 'MNF001')->first());
    }

    public function test_quantity_starts_at_one_piece_or_the_minimum_order(): void
    {
        $this->get('/product/mnf001')->assertSee('<span x-text="qty">1</span>', false);

        app(SiteSettings::class)->save([SettingKey::MinOrder->value => '5']);

        $this->get('/product/mnf001')->assertSee('<span x-text="qty">5</span>', false);
    }
}
