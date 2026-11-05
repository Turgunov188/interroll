<?php

namespace Tests\Feature;

use App\Actions\Products\ExportProducts;
use App\Enums\UserRole;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogPdfSeeder;
use Database\Seeders\CatalogSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ProductExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([CatalogSeeder::class, CatalogPdfSeeder::class]);
    }

    /** @return list<list<mixed>> */
    private function rows(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();

        return $rows;
    }

    public function test_the_file_has_every_product_with_names_in_four_languages(): void
    {
        $response = app(ExportProducts::class)->download(Product::query()->ordered());
        $rows = $this->rows($response->getFile()->getPathname());

        $this->assertSame(['Фото', 'Артикул', 'Название (RU)', 'Название (UZ)', 'Название (EN)', 'Название (TR)'], array_slice($rows[0], 0, 6));
        $this->assertCount(Product::query()->count() + 1, $rows);

        $clip = collect($rows)->first(fn (array $row) => $row[1] === 'MNF001');
        $this->assertSame(['Клупп Monforts стандартный', 'Monforts klips standart', 'Monforts clip, standard', 'Monforts klips standart'], array_slice($clip, 2, 4));
        $this->assertSame(['Запчасти для стентеров', 'Клуппы', 'Monforts'], array_slice($clip, 6, 3));
        $this->assertCount(14, $rows[0]);

        // every product with a photo gets its picture in the first column
        $zip = new \ZipArchive;
        $zip->open($response->getFile()->getPathname());
        $withPhotos = Product::query()->has('mainPhoto')->count();
        $this->assertGreaterThan(100, $withPhotos);
        $this->assertSame($withPhotos, substr_count((string) $zip->getFromName('xl/drawings/drawing1.xml'), '<xdr:pic>'));
        $this->assertNotFalse($zip->getFromName('xl/media/image1.jpeg'));
        $this->assertStringContainsString('<drawing r:id="rIdPictures"/>', (string) $zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
    }

    public function test_the_admin_button_downloads_what_the_table_shows(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Owner]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListProducts::class)
            ->set('tableSearch', 'MNF')
            ->callAction('excel')
            ->assertFileDownloaded('products-'.now()->format('Y-m-d').'.xlsx');
    }
}
