<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Interactive configurator on the product page: the visitor sets the sizes and sees a
 * live drawing and 3D model (front-end: build-tools/shapes.js). A product without one
 * shows only its uploaded drawings, 3D model and photos.
 */
enum Configurator: string implements HasLabel
{
    /** Diameter / length / coating — the original roller configurator of the design. */
    case Roller = 'roller';

    /** Wheel, tyre on a disc, guide roller: D, d, B, T. */
    case Wheel = 'wheel';

    /** Bushing, sleeve, die spring: D, d, L. */
    case Bushing = 'bushing';

    /** Plate, scraper, blade, sheet, pad: L, W, T. */
    case Plate = 'plate';

    /** Coupling spider: D, d, z, B. */
    case Spider = 'spider';

    public function getLabel(): string
    {
        return match ($this) {
            self::Roller => 'Вал — диаметр, длина, покрытие',
            self::Wheel => 'Колесо / бандаж — D, d, ширина, толщина ПУ',
            self::Bushing => 'Втулка / пружина — D, d, длина',
            self::Plate => 'Пластина / скребок / лист — длина, ширина, толщина',
            self::Spider => 'Звёздочка муфты — D, d, число лучей, толщина',
        };
    }

    /**
     * Size sliders for the shape configurators (the roller has its own controls in the design).
     *
     * @return list<array{key: string, label: string, min: int, max: int, step: int, default: int, unit: string}>
     */
    public function parameters(Locale $locale): array
    {
        $p = fn (string $key, string $label, int $min, int $max, int $step, int $default, string $unit = 'mm'): array => compact('key', 'label', 'min', 'max', 'step', 'default', 'unit');
        $t = fn (string $name): string => self::LABELS[$name][$locale->value] ?? self::LABELS[$name][Locale::main()->value];

        return match ($this) {
            self::Roller => [],
            self::Wheel => [
                $p('D', $t('outer'), 40, 600, 5, 150),
                $p('d', $t('bore'), 10, 300, 1, 40),
                $p('B', $t('width_b'), 10, 300, 1, 50),
                $p('T', $t('pu_thickness'), 3, 80, 1, 15),
            ],
            self::Bushing => [
                $p('D', $t('outer'), 10, 300, 1, 60),
                $p('d', $t('bore'), 4, 250, 1, 30),
                $p('L', $t('length'), 5, 500, 1, 60),
            ],
            self::Plate => [
                $p('L', $t('length'), 50, 3000, 10, 1000),
                $p('W', $t('width_w'), 20, 1500, 5, 150),
                $p('T', $t('thickness_t'), 2, 100, 1, 10),
            ],
            self::Spider => [
                $p('D', $t('outer'), 20, 300, 1, 90),
                $p('d', $t('bore'), 5, 150, 1, 30),
                $p('z', $t('jaws'), 3, 12, 1, 6, ''),
                $p('B', $t('thickness_b'), 5, 100, 1, 25),
            ],
        };
    }

    /** Coating materials offered by the configurator (front-end codes). */
    private const COATINGS = ['pu' => 'Полиуретан', 'nbr' => 'Резина NBR', 'epdm' => 'Резина EPDM', 'sil' => 'Силикон'];

    /** Typed sizes (forms) can exceed the sliders, so only nonsense is cut off. */
    private const MAX_SIZE = 100000;

    /** Roller sizes: the design has its own sliders, the limits here mirror them. */
    private const ROLLER_SIZES = [
        'D' => ['Диаметр', 40, 400],
        'L' => ['Рабочая длина', 100, 2000],
        'T' => ['Толщина покрытия', 5, 40],
    ];

    /**
     * What the customer picked, as readable rows for the manager:
     * ['Материал' => 'Полиуретан', 'Твёрдость' => '90 Shore A', 'Наружный диаметр D' => '150 mm'].
     * Only known keys are kept. Values may be typed by hand ("800 мм", "120,5"): the number is
     * taken out of the text; slider values are clamped to the slider limits.
     *
     * @param  array<string, string|int|float>  $values
     * @return array<string, string>
     */
    public function describeSelection(array $values, Locale $locale = Locale::Ru): array
    {
        $rows = [];

        if (isset(self::COATINGS[$values['mat'] ?? null])) {
            $rows['Материал'] = self::COATINGS[$values['mat']];
        }

        if (is_numeric($values['shore'] ?? null)) {
            $rows['Твёрдость'] = max(10, min(100, (int) $values['shore'])).' Shore A';
        }

        $sizes = $this === self::Roller
            ? array_map(fn (array $s): array => ['label' => $s[0], 'min' => $s[1], 'max' => $s[2], 'unit' => 'mm'], self::ROLLER_SIZES)
            : collect($this->parameters($locale))->keyBy('key')->all();

        foreach ($sizes as $key => $size) {
            $raw = $values[$key] ?? null;

            if (is_int($raw) || is_float($raw)) {
                $value = max($size['min'], min($size['max'], (int) round($raw)));
            } elseif (is_string($raw) && preg_match('/\d+(?:[.,]\d+)?/', $raw, $m)) {
                $number = (float) str_replace(',', '.', $m[0]);
                $value = $number > 0 && $number <= self::MAX_SIZE ? rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.') : null;
            } else {
                $value = null;
            }

            if ($value !== null) {
                $rows[$size['label']] = trim($value.' '.$size['unit']);
            }
        }

        return $rows;
    }

    private const LABELS = [
        'outer' => ['ru' => 'Наружный диаметр D', 'uz' => 'Tashqi diametr D', 'en' => 'Outer diameter D', 'tr' => 'Dış çap D'],
        'bore' => ['ru' => 'Внутренний диаметр d', 'uz' => 'Ichki diametr d', 'en' => 'Bore d', 'tr' => 'İç çap d'],
        'width_b' => ['ru' => 'Ширина B', 'uz' => 'Kenglik B', 'en' => 'Width B', 'tr' => 'Genişlik B'],
        'width_w' => ['ru' => 'Ширина W', 'uz' => 'Kenglik W', 'en' => 'Width W', 'tr' => 'Genişlik W'],
        'pu_thickness' => ['ru' => 'Толщина полиуретана T', 'uz' => 'Poliuretan qalinligi T', 'en' => 'Polyurethane thickness T', 'tr' => 'Poliüretan kalınlığı T'],
        'length' => ['ru' => 'Длина L', 'uz' => 'Uzunlik L', 'en' => 'Length L', 'tr' => 'Uzunluk L'],
        'thickness_t' => ['ru' => 'Толщина T', 'uz' => 'Qalinlik T', 'en' => 'Thickness T', 'tr' => 'Kalınlık T'],
        'thickness_b' => ['ru' => 'Толщина B', 'uz' => 'Qalinlik B', 'en' => 'Thickness B', 'tr' => 'Kalınlık B'],
        'jaws' => ['ru' => 'Число лучей z', 'uz' => 'Nurlar soni z', 'en' => 'Number of jaws z', 'tr' => 'Diş sayısı z'],
    ];
}
