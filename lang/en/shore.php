<?php

// Translation of lang/ru/shore.php (same keys).
return [
    'category' => 'Selection',
    'title' => 'How to choose Shore A hardness?',
    'excerpt' => 'The wrong hardness is the most common reason a part wears out fast. Here is a practical guide to choosing it.',
    'author' => '[Author — chief engineer]',
    'minutes' => 6,
    'cover' => 'Measuring a polyurethane roller\'s hardness with a durometer',
    'sections' => [
        'what' => 'What is Shore A?',
        'scale' => 'Feel the scale',
        'mistake' => 'The "harder is better" mistake',
        'choice' => 'Choosing by conditions',
        'summary' => 'Conclusion',
    ],
    'what' => [
        'Shore A is the hardness scale for elastic materials (rubber, polyurethane). The instrument — a durometer — presses a needle into the material and measures how deep it goes. The result runs from 0 to 100: the higher the number, the harder the material.',
        'Hardness defines how a part behaves: how well it "grips" a surface, keeps its shape under load, resists abrasion and damps vibration.',
    ],
    'scale' => [
        'intro' => 'Numbers can feel abstract. Move the slider and compare each hardness with familiar things:',
        'label' => 'Interactive scale',
        'aria' => 'Shore A value',
        'feels' => 'Roughly like',
        'used' => 'Used in parts',
        'note' => 'Comparisons are approximate — the exact value is measured for each material.',
        'points' => [
            [
                30,
                'Rubber band',
                'Very soft seals',
            ],
            [
                45,
                'Eraser',
                'Soft grip rollers',
            ],
            [
                60,
                'Soft shoe sole',
                'Ink and laminating rollers',
            ],
            [
                72,
                'Car tyre',
                'Grip and pull rollers',
            ],
            [
                82,
                'Shoe heel',
                'Conveyor rollers, bushings',
            ],
            [
                90,
                'Hard polyurethane wheel',
                'Wheels, scrapers',
            ],
            [
                96,
                'Skateboard wheel',
                'Press and rolling rollers, springs',
            ],
        ],
    ],
    'mistake' => [
        'intro' => 'Many people ask for the hardest material so the part lasts longer. But a roller that is too hard:',
        'items' => [
            'grips the surface poorly and slips;',
            'doesn\'t absorb shocks — bearings and the core wear faster;',
            'in printing it transfers ink unevenly.',
        ],
        'outro' => 'Conversely, a material that is too soft deforms under heavy load, overheats and starts to crack.',
    ],
    'choice' => [
        'intro' => 'The general guide is as follows (the engineer sets the final value):',
        'items' => [
            [
                '60–75 A',
                '— gripping, ink transfer, laminating, delicate surfaces;',
            ],
            [
                '80–90 A',
                '— conveyor and drive rollers, general-purpose work;',
            ],
            [
                '92–95 A',
                '— press, rolling, heavy load and abrasive environments.',
            ],
        ],
        'outro' => 'Temperature, oil and water matter too: they decide the material, and hardness is then chosen within that material.',
    ],
    'summary' => 'The right hardness is a balance between grip, durability and load. If you\'re unsure, describe your operating conditions and we\'ll recommend one.',
];
