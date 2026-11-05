<?php

namespace App\Actions\Products;

use App\Enums\Locale;
use App\Models\Product;
use App\Support\XlsxImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Products → .xlsx for the admin «Скачать Excel» buttons: what the table shows (filters, search, sort)
 * or the rows ticked in it. The main photo sits in the first column, then the names in all four languages.
 */
final class ExportProducts
{
    /** heading => column width */
    private const COLUMNS = [
        'Фото' => 11, 'Артикул' => 14, 'Название (RU)' => 42, 'Название (UZ)' => 36, 'Название (EN)' => 36, 'Название (TR)' => 36,
        'Раздел' => 26, 'Подраздел' => 28, 'Станок' => 16, 'Материал' => 14, 'Твёрдость' => 14,
        'Краткое описание (RU)' => 60, 'Цена, сум' => 14, 'На сайте' => 10,
    ];

    /** pixels of the picture in the «Фото» column; its rows are made tall enough for it */
    private const PHOTO = 64;

    public function download(Builder $query, string $name = 'products'): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'products').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Товары');
        $sheet->setSheetView((new SheetView)->setFreezeRow(2));
        foreach (array_values(self::COLUMNS) as $i => $width) {
            $sheet->setColumnWidth($width, $i + 1);
        }

        $writer->addRow(Row::fromValues(array_keys(self::COLUMNS), (new Style)->setFontBold()->setBackgroundColor('F5B700')));

        $rows = 1;
        $photos = [];
        $style = (new Style)->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
        $disk = Storage::disk('public');
        $query->with(['category.parent', 'machineBrand', 'mainPhoto'])
            ->lazy(200)
            ->each(function (Product $product) use ($writer, $disk, $style, &$rows, &$photos): void {
                $row = Row::fromValues($this->row($product), $style);
                if ($path = $product->mainPhoto?->path) {
                    $photos[$rows] = $disk->path($path);
                    $row->setHeight(self::PHOTO * 0.75 + 6); // points
                }
                $writer->addRow($row);
                $rows++;
            });

        $sheet->setAutoFilter(new AutoFilter(0, 1, count(self::COLUMNS) - 1, $rows));
        $writer->close();

        XlsxImages::embed($path, $photos, column: 0, size: self::PHOTO);

        return response()
            ->download($path, $name.'-'.now()->format('Y-m-d').'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->deleteFileAfterSend();
    }

    /** @return list<string|int|float|null> */
    private function row(Product $product): array
    {
        $category = $product->category;
        $section = $category?->parent ?? $category;

        return [
            '', // the picture is drawn over this cell
            $product->sku,
            ...array_map(fn (Locale $locale): string => (string) ($product->name[$locale->value] ?? ''), [Locale::Ru, Locale::Uz, Locale::En, Locale::Tr]),
            (string) $section?->tr('name', Locale::Ru),
            $category?->parent ? (string) $category->tr('name', Locale::Ru) : '',
            (string) $product->machineBrand?->name,
            $product->material->label(Locale::Ru),
            (string) $product->hardnessLabel(),
            (string) ($product->short[Locale::Ru->value] ?? ''),
            $product->price_on_request || $product->price === null ? 'по запросу' : (float) $product->price,
            $product->is_active ? 'да' : 'нет',
        ];
    }
}
