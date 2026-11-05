<?php

// Translation of lang/ru/recoating.php (same keys).
return [
    'breadcrumb_services' => 'Services',
    'breadcrumb' => 'Recoating',
    'hero' => [
        'lines' => [
            'Don\'t throw away',
            'your old roller.',
        ],
        'accent' => 'We\'ll recoat it.',
        'lead' => 'We remove the worn coating, restore the metal core and cast new polyurethane or rubber on it. The roller works like new — cheaper and faster than buying a new one.',
        'send_photo' => 'Send roller photo →',
        'savings' => 'Savings calculator',
    ],
    'process' => [
        'title' => 'Process · live diagram',
        'label' => 'Recoating process: :stage',
        'core' => 'CORE — REUSED',
        'worn' => 'OLD COATING — WORN',
        'stripped' => 'COATING REMOVED',
        'new_layer' => ':material — NEW LAYER',
        'stages' => [
            'Worn roller',
            'Coating removal',
            'Casting the new layer',
            'Grinding and inspection',
        ],
    ],
    'calculator' => [
        'eyebrow' => '[01] — Savings calculator',
        'title' => 'Calculate your savings',
        'price' => 'New roller price (1 pc), UZS',
        'quantity' => 'Number of rollers',
        'ratio' => 'Recoating price vs new',
        'note' => 'Rough estimate. The exact price depends on the roller\'s condition, size and material — send a photo and we\'ll calculate it precisely.',
        'new' => 'New rollers',
        'recoated' => 'Recoating',
        'currency' => 'UZS',
        'saving' => 'Your savings',
        'cheaper' => 'UZS · :percent% less',
        'action' => 'Get the exact price →',
    ],
    'steps' => [
        'eyebrow' => '[02] — How we work',
        'title' => 'From intake to return',
        'items' => [
            [
                'title' => 'Intake',
                'text' => 'The roller is brought to the factory or we collect it.',
                'time' => '1 day',
            ],
            [
                'title' => 'Diagnostics',
                'text' => 'The core and bearing seats are inspected and measured.',
                'time' => ':recoat_diag_hours hours',
            ],
            [
                'title' => 'Removing the old',
                'text' => 'The old coating is removed mechanically or thermally.',
                'time' => ':recoat_strip_hours hours',
            ],
            [
                'title' => 'Core preparation',
                'text' => 'Cleaning, sandblasting, applying the adhesive layer.',
                'time' => ':recoat_prep_hours hours',
            ],
            [
                'title' => 'Casting',
                'text' => 'New polyurethane or rubber is cast and cured.',
                'time' => ':recoat_cast_days days',
            ],
            [
                'title' => 'Grinding and QC',
                'text' => 'Ground to exact size, hardness measured.',
                'time' => ':recoat_finish_hours hours',
            ],
        ],
    ],
    'materials' => [
        'eyebrow' => '[03] — Coating material',
        'title' => 'Choose a material for your operating conditions',
        'note' => 'General comparison. An engineer will give an exact recommendation for your conditions.',
        'items' => [
            'pu' => [
                'hardness' => '70–95 A',
                'text' => 'The most resistant to abrasion and load. For conveyor, press and rolling rollers.',
                'use' => 'Conveyor · press · rolling',
            ],
            'nbr' => [
                'hardness' => '60–85 A',
                'text' => 'Resistant to oils and greases. For printing rollers and rollers in oily environments.',
                'use' => 'Printing · oily environment',
            ],
            'epdm' => [
                'hardness' => '50–80 A',
                'text' => 'Resists heat, water, steam and weather. For washdown and hot environments.',
                'use' => 'Washdown · steam · outdoors',
            ],
            'sil' => [
                'hardness' => '30–80 A',
                'text' => 'Withstands high temperatures, non-stick. For laminating and heat treatment.',
                'use' => 'Laminating · hot pressing',
            ],
        ],
    ],
    'result' => [
        'eyebrow' => '[04] — Results',
        'title' => 'Before and after',
        'text' => 'Drag the line to compare a worn roller with the same roller after recoating.',
        'cases' => [
            'Conveyor roller',
            'Printing roller',
            'Press roller',
        ],
        'before' => 'BEFORE — :case',
        'after' => 'AFTER — :case',
    ],
    'shipping' => [
        'eyebrow' => '[05] — Outside Tashkent?',
        'title' => 'Sending a roller to the factory — 3 steps',
        'step' => 'STEP :n',
        'items' => [
            [
                'title' => 'Send a photo and dimensions',
                'text' => 'Send a photo of the roller, its diameter and length via Telegram or the form.',
            ],
            [
                'title' => 'Send the roller',
                'text' => 'Via :carrier or bring it yourself. Pickup within Tashkent — :pickup_terms.',
            ],
            [
                'title' => 'Get your finished roller',
                'text' => 'Returned with a test report and warranty card.',
            ],
        ],
    ],
    'request' => [
        'eyebrow' => '[ Request ]',
        'title' => 'Send a photo of the roller — we\'ll quote price and lead time',
        'text' => 'A photo and rough dimensions are enough. An engineer will get back to you within :reply_hours hours.',
        'name' => 'Name / company',
        'diameter' => 'Diameter, mm',
        'length' => 'Length, mm',
        'quantity' => 'Quantity',
        'material' => 'Selected material',
        'photo' => 'Roller photo',
    ],
    'faq' => [
        'title' => 'Recoating FAQ',
        'items' => [
            [
                'q' => 'Can any roller be recoated?',
                'a' => 'If the metal core isn\'t bent or badly damaged — yes. We\'ll confirm after diagnostics.',
            ],
            [
                'q' => 'How many times can it be recoated?',
                'a' => 'Several times, if the core is in good condition. :recoat_times',
            ],
            [
                'q' => 'Is a recoated roller as good as new?',
                'a' => 'The new layer is cast exactly as on a new roller and passes the same inspection. :warranty_terms',
            ],
            [
                'q' => 'Can the material be changed?',
                'a' => 'Yes. For example, a rubber coating can be replaced with tougher polyurethane — the engineer will advise.',
            ],
        ],
    ],
];
