<?php

namespace Database\Seeders;

use App\Enums\Configurator;
use App\Enums\Material;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Starting catalog: the categories and parts shown in the approved design, in 4 languages.
 * Safe to run again — rows are matched by slug / SKU and updated, nothing is duplicated.
 *
 *   php artisan db:seed --class=CatalogSeeder
 */
class CatalogSeeder extends Seeder
{
    /** Which industries each part is offered for (catalog «Отрасль» filter, product «Применение» tab). */
    private const INDUSTRIES = [
        'metallurgiya' => ['IR-V01', 'IR-V03', 'IR-K01', 'IR-K02', 'IR-P01', 'IR-P03', 'IR-M01', 'IR-M02', 'IR-S01'],
        'poligrafiya' => ['IR-V01', 'IR-V05', 'IR-K01', 'IR-K02', 'IR-R01'],
        'oziq-ovqat' => ['IR-V02', 'IR-R01', 'IR-P02', 'IR-X01'],
        'avtomobil' => ['IR-A01', 'IR-A02', 'IR-M02', 'IR-S01'],
        'ombor' => ['IR-G01', 'IR-G02', 'IR-R01', 'IR-V02', 'IR-P01'],
    ];

    /** Size configurator per part: rollers, wheels, bushings, plates, spiders. Custom parts (maxsus) have none. */
    private const CONFIGURATORS = [
        'IR-V01' => Configurator::Roller, 'IR-V02' => Configurator::Roller, 'IR-V03' => Configurator::Roller, 'IR-V05' => Configurator::Roller,
        'IR-K01' => Configurator::Roller, 'IR-K02' => Configurator::Roller,
        'IR-G01' => Configurator::Wheel, 'IR-G02' => Configurator::Wheel, 'IR-R01' => Configurator::Wheel,
        'IR-P01' => Configurator::Plate, 'IR-P02' => Configurator::Plate, 'IR-P03' => Configurator::Plate, 'IR-A02' => Configurator::Plate,
        'IR-M02' => Configurator::Bushing, 'IR-S01' => Configurator::Bushing, 'IR-A01' => Configurator::Bushing,
        'IR-M01' => Configurator::Spider,
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $industries = collect(self::industries())->mapWithKeys(fn (array $row): array => [
                $row['slug'] => Industry::updateOrCreate(['slug' => $row['slug']], $row),
            ]);

            $categories = collect(self::categories())->mapWithKeys(fn (array $row): array => [
                $row['slug'] => Category::updateOrCreate(['slug' => $row['slug']], $row + ['is_active' => true]),
            ]);

            foreach (self::products() as $row) {
                $category = $categories[$row['category']];
                unset($row['category']);
                $row['material'] = Material::from($row['material']);
                $row['configurator'] = self::CONFIGURATORS[$row['sku']] ?? null;

                $product = Product::withTrashed()->updateOrCreate(['sku' => $row['sku']], $row + [
                    'category_id' => $category->id,
                    'hardness_scale' => 'A',
                    'price_on_request' => true,
                    'made_to_order' => true,
                    'is_active' => true,
                ]);

                $product->industries()->syncWithoutDetaching(
                    $industries
                        ->filter(fn (Industry $industry): bool => in_array($row['sku'], self::INDUSTRIES[$industry->slug] ?? [], true))
                        ->map(fn (Industry $industry): int => $industry->getKey())
                        ->values()
                        ->all(),
                );
            }
        });

        // Pivot syncs fire no model events, so refresh the public catalog explicitly.
        app(CatalogCache::class)->flush();
    }

    /** @return list<array<string, mixed>> */
    private static function industries(): array
    {
        return [
            ['slug' => 'metallurgiya', 'sort' => 1, 'name' => ['ru' => 'Металлургия', 'uz' => 'Metallurgiya', 'en' => 'Metallurgy', 'tr' => 'Metalurji']],
            ['slug' => 'poligrafiya', 'sort' => 2, 'name' => ['ru' => 'Полиграфия', 'uz' => 'Poligrafiya', 'en' => 'Printing', 'tr' => 'Matbaa']],
            ['slug' => 'oziq-ovqat', 'sort' => 3, 'name' => ['ru' => 'Пищевая промышленность', 'uz' => 'Oziq-ovqat', 'en' => 'Food industry', 'tr' => 'Gıda']],
            ['slug' => 'avtomobil', 'sort' => 4, 'name' => ['ru' => 'Автомобильная', 'uz' => 'Avtomobil', 'en' => 'Automotive', 'tr' => 'Otomotiv']],
            ['slug' => 'ombor', 'sort' => 5, 'name' => ['ru' => 'Склад и логистика', 'uz' => 'Ombor va logistika', 'en' => 'Warehousing and logistics', 'tr' => 'Depo ve lojistik']],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function categories(): array
    {
        return [
            ['slug' => 'valik', 'sort' => 1, 'name' => ['ru' => 'Валы', 'uz' => 'Valiklar', 'en' => 'Rollers', 'tr' => 'Merdaneler']],
            ['slug' => 'gildirak', 'sort' => 2, 'name' => ['ru' => 'Колёса', 'uz' => 'G\'ildiraklar', 'en' => 'Wheels', 'tr' => 'Tekerlekler']],
            ['slug' => 'qoplash', 'sort' => 3, 'name' => ['ru' => 'Восстановление покрытия', 'uz' => 'Qayta qoplash', 'en' => 'Recoating', 'tr' => 'Yeniden kaplama']],
            ['slug' => 'plastina', 'sort' => 4, 'name' => ['ru' => 'Пластина и скребок', 'uz' => 'Plastina va qirg\'ich', 'en' => 'Plate and scraper', 'tr' => 'Plaka ve sıyırıcı']],
            ['slug' => 'mufta', 'sort' => 5, 'name' => ['ru' => 'Муфта и пружина', 'uz' => 'Mufta va prujina', 'en' => 'Coupling and spring', 'tr' => 'Kaplin ve yay']],
            ['slug' => 'avto', 'sort' => 6, 'name' => ['ru' => 'Авто', 'uz' => 'Avto', 'en' => 'Auto', 'tr' => 'Otomotiv']],
            ['slug' => 'maxsus', 'sort' => 7, 'name' => ['ru' => 'Спецзаказ', 'uz' => 'Maxsus', 'en' => 'Custom', 'tr' => 'Özel']],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function products(): array
    {
        return [
            ['sku' => 'IR-V01',
                'slug' => 'ir-v01',
                'category' => 'valik',
                'material' => 'pu',
                'hardness' => 90,
                'hardness_max' => null,
                'sort' => 1,
                'is_featured' => true,
                'name' => ['ru' => 'Полиуретановый вал', 'uz' => 'Poliuretan valik', 'en' => 'Polyurethane roller', 'tr' => 'Poliüretan merdane'],
                'short' => ['ru' => 'С металлическим сердечником, под размер заказа · Ø [__]–[___] мм', 'uz' => 'Metall o\'zakli, buyurtma o\'lchamida · Ø [__]–[___] mm', 'en' => 'Metal core, made to size · Ø [__]–[___] mm', 'tr' => 'Metal göbekli, siparişe özel ölçü · Ø [__]–[___] mm'],
                'description' => ['ru' => 'Вал с полиуретановым покрытием, отлитым на металлический сердечник. Стоек к истиранию, маслу и нагрузке. Изготавливается под размер заказа для конвейерных, прессовых, прокатных и полиграфических линий.', 'uz' => 'Metall o\'zak ustiga quyilgan poliuretan qoplamali valik. Ishqalanish, yog\' va yuklamaga chidamli. Konveyer, press, prokat va poligrafiya liniyalari uchun buyurtma o\'lchamida tayyorlanadi.', 'en' => 'A roller with a polyurethane coating cast onto a metal core. Resistant to abrasion, oil and load. Made to order for conveyor, press, rolling and printing lines.', 'tr' => 'Metal göbek üzerine dökülmüş poliüretan kaplamalı merdane. Aşınmaya, yağa ve yüke dayanıklı. Konveyör, pres, haddeleme ve matbaa hatları için siparişe özel ölçüde üretilir.']],
            ['sku' => 'IR-V02',
                'slug' => 'ir-v02',
                'category' => 'valik',
                'material' => 'pu',
                'hardness' => 85,
                'hardness_max' => null,
                'sort' => 2,
                'is_featured' => true,
                'name' => ['ru' => 'Конвейерный ролик', 'uz' => 'Konveyer roligi', 'en' => 'Conveyor roller', 'tr' => 'Konveyör makarası'],
                'short' => ['ru' => 'Для ленточных конвейеров, с подшипником', 'uz' => 'Lenta konveyerlari uchun, podshipnik bilan', 'en' => 'For belt conveyors, with bearing', 'tr' => 'Bant konveyörler için, rulmanlı']],
            ['sku' => 'IR-V03',
                'slug' => 'ir-v03',
                'category' => 'valik',
                'material' => 'pu',
                'hardness' => 95,
                'hardness_max' => null,
                'sort' => 3,
                'is_featured' => true,
                'name' => ['ru' => 'Прессовый вал', 'uz' => 'Press valigi', 'en' => 'Press roller', 'tr' => 'Pres merdanesi'],
                'short' => ['ru' => 'Стойкость к высокому давлению и истиранию', 'uz' => 'Yuqori bosim va ishqalanishga chidamli', 'en' => 'Withstands high pressure and abrasion', 'tr' => 'Yüksek basınç ve aşınmaya dayanıklı']],
            ['sku' => 'IR-V05',
                'slug' => 'ir-v05',
                'category' => 'valik',
                'material' => 'rubber',
                'hardness' => 70,
                'hardness_max' => null,
                'sort' => 4,
                'is_featured' => false,
                'name' => ['ru' => 'Красочный и офсетный вал', 'uz' => 'Bo\'yoq va ofset valigi', 'en' => 'Ink and offset roller', 'tr' => 'Boya ve ofset merdanesi'],
                'short' => ['ru' => 'Резина, стойкая к краскам и растворителям', 'uz' => 'Bo\'yoq va erituvchilarga chidamli rezina', 'en' => 'Rubber resistant to inks and solvents', 'tr' => 'Boya ve solventlere dayanıklı kauçuk']],
            ['sku' => 'IR-G01',
                'slug' => 'ir-g01',
                'category' => 'gildirak',
                'material' => 'pu',
                'hardness' => 92,
                'hardness_max' => null,
                'sort' => 5,
                'is_featured' => false,
                'name' => ['ru' => 'Полиуретановое колесо', 'uz' => 'Poliuretan g\'ildirak', 'en' => 'Polyurethane wheel', 'tr' => 'Poliüretan tekerlek'],
                'short' => ['ru' => 'Для тележек и погрузчиков', 'uz' => 'Arava va yuk ko\'targichlar uchun', 'en' => 'For carts and forklifts', 'tr' => 'Araba ve forkliftler için']],
            ['sku' => 'IR-G02',
                'slug' => 'ir-g02',
                'category' => 'gildirak',
                'material' => 'pu',
                'hardness' => 95,
                'hardness_max' => null,
                'sort' => 6,
                'is_featured' => false,
                'name' => ['ru' => 'Бандаж (покрытие диска)', 'uz' => 'Bandaj (disk qoplamasi)', 'en' => 'Tyre (disc coating)', 'tr' => 'Bandaj (jant kaplaması)'],
                'short' => ['ru' => 'Заливка на диск заказчика', 'uz' => 'Mijoz diski ustiga quyish', 'en' => 'Cast onto the customer\'s disc', 'tr' => 'Müşteri jantı üzerine döküm']],
            ['sku' => 'IR-R01',
                'slug' => 'ir-r01',
                'category' => 'gildirak',
                'material' => 'pu',
                'hardness' => 90,
                'hardness_max' => null,
                'sort' => 7,
                'is_featured' => false,
                'name' => ['ru' => 'Направляющий ролик', 'uz' => 'Yo\'naltiruvchi rolik', 'en' => 'Guide roller', 'tr' => 'Kılavuz makara'],
                'short' => ['ru' => 'С подшипником · Ø [__] мм', 'uz' => 'Podshipnikli · Ø [__] mm', 'en' => 'With bearing · Ø [__] mm', 'tr' => 'Rulmanlı · Ø [__] mm']],
            ['sku' => 'IR-K01',
                'slug' => 'ir-k01',
                'category' => 'qoplash',
                'material' => 'pu',
                'hardness' => 70,
                'hardness_max' => 95,
                'sort' => 8,
                'is_featured' => false,
                'name' => ['ru' => 'Восстановление покрытия вала', 'uz' => 'Valikni qayta qoplash', 'en' => 'Roller recoating', 'tr' => 'Merdane yeniden kaplama'],
                'short' => ['ru' => 'Новый полиуретан или резина на старый сердечник', 'uz' => 'Eski o\'zakka yangi poliuretan yoki rezina', 'en' => 'New polyurethane or rubber on the old core', 'tr' => 'Eski göbeğe yeni poliüretan veya kauçuk']],
            ['sku' => 'IR-K02',
                'slug' => 'ir-k02',
                'category' => 'qoplash',
                'material' => 'rubber',
                'hardness' => 60,
                'hardness_max' => 80,
                'sort' => 9,
                'is_featured' => false,
                'name' => ['ru' => 'Обрезинивание', 'uz' => 'Rezina bilan qoplash', 'en' => 'Rubber coating', 'tr' => 'Kauçuk kaplama'],
                'short' => ['ru' => 'NBR / EPDM, методом вулканизации', 'uz' => 'NBR / EPDM, vulkanizatsiya usulida', 'en' => 'NBR / EPDM, vulcanised', 'tr' => 'NBR / EPDM, vulkanizasyon yöntemiyle']],
            ['sku' => 'IR-P01',
                'slug' => 'ir-p01',
                'category' => 'plastina',
                'material' => 'pu',
                'hardness' => 90,
                'hardness_max' => null,
                'sort' => 10,
                'is_featured' => false,
                'name' => ['ru' => 'Скребковая пластина', 'uz' => 'Qirg\'ich plastina', 'en' => 'Scraper plate', 'tr' => 'Sıyırıcı plaka'],
                'short' => ['ru' => 'Для очистки ленты и конвейера', 'uz' => 'Lenta va konveyer tozalash uchun', 'en' => 'For cleaning belts and conveyors', 'tr' => 'Bant ve konveyör temizliği için']],
            ['sku' => 'IR-P02',
                'slug' => 'ir-p02',
                'category' => 'plastina',
                'material' => 'pu',
                'hardness' => 85,
                'hardness_max' => null,
                'sort' => 11,
                'is_featured' => false,
                'name' => ['ru' => 'Очистной нож', 'uz' => 'Tozalovchi pichoq', 'en' => 'Cleaning blade', 'tr' => 'Temizleme bıçağı'],
                'short' => ['ru' => 'Для очистки бункеров и ёмкостей', 'uz' => 'Bunker va idishlarni qirish uchun', 'en' => 'For scraping hoppers and tanks', 'tr' => 'Bunker ve tank sıyırma için']],
            ['sku' => 'IR-P03',
                'slug' => 'ir-p03',
                'category' => 'plastina',
                'material' => 'pu',
                'hardness' => 80,
                'hardness_max' => 95,
                'sort' => 12,
                'is_featured' => false,
                'name' => ['ru' => 'Полиуретановый лист', 'uz' => 'Poliuretan list', 'en' => 'Polyurethane sheet', 'tr' => 'Poliüretan levha'],
                'short' => ['ru' => 'Толщина [__]–[__] мм, режем по размеру', 'uz' => 'Qalinlik [__]–[__] mm, kesib beriladi', 'en' => 'Thickness [__]–[__] mm, cut to size', 'tr' => 'Kalınlık [__]–[__] mm, ölçüye kesilir']],
            ['sku' => 'IR-M01',
                'slug' => 'ir-m01',
                'category' => 'mufta',
                'material' => 'pu',
                'hardness' => 92,
                'hardness_max' => null,
                'sort' => 13,
                'is_featured' => false,
                'name' => ['ru' => 'Звёздочка муфты', 'uz' => 'Mufta yulduzchasi', 'en' => 'Coupling spider', 'tr' => 'Kaplin yıldızı'],
                'short' => ['ru' => 'Для муфт насосов и редукторов', 'uz' => 'Nasos va reduktor muftalari uchun', 'en' => 'For pump and gearbox couplings', 'tr' => 'Pompa ve redüktör kaplinleri için']],
            ['sku' => 'IR-M02',
                'slug' => 'ir-m02',
                'category' => 'mufta',
                'material' => 'rubber',
                'hardness' => 75,
                'hardness_max' => null,
                'sort' => 14,
                'is_featured' => false,
                'name' => ['ru' => 'Резина муфты', 'uz' => 'Mufta rezinasi', 'en' => 'Coupling rubber', 'tr' => 'Kaplin kauçuğu'],
                'short' => ['ru' => 'Втулки для пальцевых муфт', 'uz' => 'Barmoqli muftalar uchun vtulkalar', 'en' => 'Bushings for pin couplings', 'tr' => 'Pimli kaplinler için burçlar']],
            ['sku' => 'IR-S01',
                'slug' => 'ir-s01',
                'category' => 'mufta',
                'material' => 'pu',
                'hardness' => 90,
                'hardness_max' => null,
                'sort' => 15,
                'is_featured' => false,
                'name' => ['ru' => 'Штамповая пружина', 'uz' => 'Shtamp prujinasi', 'en' => 'Die spring', 'tr' => 'Kalıp yayı'],
                'short' => ['ru' => 'Вместо металлической пружины, бесшумно', 'uz' => 'Metall prujina o\'rniga, shovqinsiz', 'en' => 'Replaces metal springs, silent', 'tr' => 'Metal yay yerine, sessiz']],
            ['sku' => 'IR-A01',
                'slug' => 'ir-a01',
                'category' => 'avto',
                'material' => 'pu',
                'hardness' => 85,
                'hardness_max' => null,
                'sort' => 16,
                'is_featured' => false,
                'name' => ['ru' => 'Втулка амортизатора', 'uz' => 'Amortizator vtulkasi', 'en' => 'Shock absorber bushing', 'tr' => 'Amortisör burcu'],
                'short' => ['ru' => 'Полиуретановая втулка для подвески', 'uz' => 'Osma tizimi uchun poliuretan vtulka', 'en' => 'Polyurethane suspension bushing', 'tr' => 'Süspansiyon için poliüretan burç']],
            ['sku' => 'IR-A02',
                'slug' => 'ir-a02',
                'category' => 'avto',
                'material' => 'rubber',
                'hardness' => 70,
                'hardness_max' => null,
                'sort' => 17,
                'is_featured' => false,
                'name' => ['ru' => 'Резиновая подушка', 'uz' => 'Rezina yostiqcha', 'en' => 'Rubber pad', 'tr' => 'Kauçuk takoz'],
                'short' => ['ru' => 'Опоры двигателя и кузова', 'uz' => 'Dvigatel va kuzov tayanchlari', 'en' => 'Engine and body mounts', 'tr' => 'Motor ve kasa takozları']],
            ['sku' => 'IR-X01',
                'slug' => 'ir-x01',
                'category' => 'maxsus',
                'material' => 'pu',
                'hardness' => 60,
                'hardness_max' => 98,
                'sort' => 18,
                'is_featured' => false,
                'name' => ['ru' => 'Деталь по чертежу', 'uz' => 'Chizma bo\'yicha detal', 'en' => 'Part made to drawing', 'tr' => 'Çizime göre parça'],
                'short' => ['ru' => 'По образцу или чертежу, любой формы', 'uz' => 'Namuna yoki chizma asosida, istalgan shakl', 'en' => 'From a sample or drawing, any shape', 'tr' => 'Numune veya çizime göre, her şekilde']],
        ];
    }
}
