<?php

namespace Tests\Feature;

use App\Actions\Products\AttachProductMedia;
use App\Enums\MediaType;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(MediaType::DISK);
        $this->seed(CatalogSeeder::class);
        $this->product = Product::where('sku', 'IR-X01')->firstOrFail(); // custom part: no configurator
    }

    /** Puts a fake file on the media disk, as Filament's upload would. */
    private function upload(MediaType $type, string $name, int $kb = 50): string
    {
        return UploadedFile::fake()->create($name, $kb)->store($type->directory(), MediaType::DISK);
    }

    private function pageData(string $uri): array
    {
        preg_match('#<script type="application/json" id="ir-data">(.*?)</script>#s', $this->get($uri)->assertOk()->getContent(), $m);

        return json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_files_are_typed_and_ordered(): void
    {
        $attach = app(AttachProductMedia::class);

        $attach->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'a.jpg'), $this->upload(MediaType::Photo, 'b.jpg')]);
        $attach->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'c.jpg')]);

        $this->assertSame([1, 2, 3], $this->product->media()->where('type', MediaType::Photo)->pluck('sort')->all());
        $this->assertStringStartsWith('products/photos/', $this->product->fresh()->mainImage());
    }

    public function test_product_page_gets_each_view_from_its_own_files(): void
    {
        $attach = app(AttachProductMedia::class);
        $attach->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'part.jpg')]);
        $attach->handle($this->product, MediaType::Drawing, [$this->upload(MediaType::Drawing, 'part.pdf', 120)], []);
        $attach->handle($this->product, MediaType::Model3d, [$this->upload(MediaType::Model3d, 'part.glb', 900)]);
        $passport = $attach->handle($this->product, MediaType::Document, [$this->upload(MediaType::Document, 'passport.pdf')])->first();
        $passport->update(['alt' => ['ru' => 'Технический паспорт', 'uz' => 'Texnik pasport']]);

        $product = $this->pageData('/product/ir-x01')['product'];

        $this->assertFalse($product['configurable']);
        $this->assertCount(1, $product['media']['photos']);
        $this->assertFalse($product['media']['drawings'][0]['isImage']);
        $this->assertStringEndsWith('.glb', $product['media']['model']['src']);
        $this->assertSame('Технический паспорт', $product['docs'][0]['title']); // documents before drawings
        $this->assertCount(2, $product['docs']);

        $uz = $this->pageData('/uz/product/ir-x01')['product'];
        $this->assertSame('Texnik pasport', $uz['docs'][0]['title']);
    }

    public function test_catalog_card_uses_the_first_photo(): void
    {
        app(AttachProductMedia::class)->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'card.jpg')]);

        $card = collect($this->pageData('/catalog')['products'])->firstWhere('code', 'IR-X01');

        $this->assertStringContainsString('/storage/products/photos/', $card['img']);
    }

    public function test_rollers_keep_the_configurator(): void
    {
        $product = $this->pageData('/product/ir-v01')['product'];

        $this->assertSame('roller', $product['configurator']);
        $this->assertSame([], $product['params']); // the roller has its own controls in the design
    }

    public function test_shape_configurators_send_their_parameters_in_the_page_language(): void
    {
        $wheel = $this->pageData('/uz/product/ir-g01')['product'];

        $this->assertSame('wheel', $wheel['configurator']);
        $this->assertSame(['D', 'd', 'B', 'T'], array_column($wheel['params'], 'key'));
        $this->assertSame('Tashqi diametr D', $wheel['params'][0]['label']);

        $this->assertSame('spider', $this->pageData('/product/ir-m01')['product']['configurator']);
        $this->assertSame('plate', $this->pageData('/product/ir-p03')['product']['configurator']);
        $this->assertSame('bushing', $this->pageData('/product/ir-a01')['product']['configurator']);
        $this->assertNull($this->pageData('/product/ir-x01')['product']['configurator']);
    }

    public function test_deleting_a_file_removes_it_from_disk(): void
    {
        $media = app(AttachProductMedia::class)->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'x.jpg')])->first();
        Storage::disk(MediaType::DISK)->assertExists($media->path);

        $media->delete();

        Storage::disk(MediaType::DISK)->assertMissing($media->path);
    }

    public function test_replacing_a_file_removes_the_old_one(): void
    {
        $media = app(AttachProductMedia::class)->handle($this->product, MediaType::Photo, [$old = $this->upload(MediaType::Photo, 'old.jpg')])->first();

        $media->update(['path' => $this->upload(MediaType::Photo, 'new.jpg')]);

        Storage::disk(MediaType::DISK)->assertMissing($old);
        $this->assertNotNull($media->fresh()->size);
    }

    public function test_force_deleting_a_product_removes_its_files_but_soft_delete_keeps_them(): void
    {
        $media = app(AttachProductMedia::class)->handle($this->product, MediaType::Photo, [$this->upload(MediaType::Photo, 'y.jpg')])->first();

        $this->product->delete();
        Storage::disk(MediaType::DISK)->assertExists($media->path);

        $this->product->forceDelete();
        Storage::disk(MediaType::DISK)->assertMissing($media->path);
        $this->assertDatabaseCount('product_media', 0);
    }
}
