<?php

// Translation of lang/ru/finder.php (same keys).
return [
    'eyebrow' => '[ Part selection wizard ]',
    'title' => 'Not sure',
    'accent' => 'which part you need?',
    'lead' => 'Answer 4 questions and we\'ll recommend a material and hardness for your conditions. 1 minute.',
    'step' => 'Step :n / 05',
    'progress' => [
        'Part type',
        'Industry',
        'Operating conditions',
        'Environment',
        'Result',
    ],
    'type' => [
        'title' => 'What part do you need?',
        'options' => [
            'roller' => [
                'title' => 'Roller',
                'text' => 'Conveyor, press, printing',
            ],
            'wheel' => [
                'title' => 'Wheel',
                'text' => 'Carts, forklifts',
            ],
            'plate' => [
                'title' => 'Plate / scraper',
                'text' => 'Cleaning, scraping',
            ],
            'coupling' => [
                'title' => 'Coupling / spring',
                'text' => 'Transmission, die',
            ],
            'unknown' => [
                'title' => 'Not sure',
                'text' => 'I have a photo or sample',
            ],
        ],
    ],
    'industry' => [
        'title' => 'Which industry is it used in?',
        'options' => [
            'metal' => [
                'title' => 'Metallurgy',
                'text' => 'Rolling, casting',
            ],
            'print' => [
                'title' => 'Printing',
                'text' => 'Ink, paper, laminating',
            ],
            'food' => [
                'title' => 'Food industry',
                'text' => 'Packaging, conveyor',
            ],
            'auto' => [
                'title' => 'Automotive',
                'text' => 'Suspension, mounts',
            ],
            'logistics' => [
                'title' => 'Warehousing and logistics',
                'text' => 'Carts, roller tables',
            ],
            'other' => [
                'title' => 'Other',
                'text' => 'Wood, construction, etc.',
            ],
        ],
    ],
    'conditions' => [
        'title' => 'How does the part work?',
        'load' => 'Load / pressure',
        'loads' => [
            'low' => [
                'title' => 'Light',
                'text' => 'grip, guiding',
            ],
            'mid' => [
                'title' => 'Medium',
                'text' => 'conveyor, drive',
            ],
            'high' => [
                'title' => 'Heavy',
                'text' => 'press, rolling',
            ],
        ],
        'abrasion' => 'Abrasion / wear',
        'abrasions' => [
            'low' => [
                'title' => 'Low',
                'text' => 'smooth surface',
            ],
            'mid' => [
                'title' => 'Medium',
                'text' => 'standard material',
            ],
            'high' => [
                'title' => 'High',
                'text' => 'sand, metal, abrasives',
            ],
        ],
    ],
    'environment' => [
        'title' => 'What\'s the environment?',
        'temperature' => 'Operating temperature',
        'options' => [
            'oil' => [
                'title' => 'Oil / grease',
                'text' => 'Contact with oil, petrol, diesel',
                'short' => 'oil',
            ],
            'water' => [
                'title' => 'Water / steam',
                'text' => 'Washdown, humidity, steam',
                'short' => 'water',
            ],
            'food' => [
                'title' => 'Food industry',
                'text' => 'Direct contact with product',
                'short' => 'food',
            ],
        ],
    ],
    'result' => [
        'label' => 'Recommendation',
        'material' => 'Material',
        'hardness' => 'Hardness',
        'why' => 'Why this?',
        'products' => 'Matching products',
        'disclaimer' => 'This is a general recommendation. A factory engineer will confirm the final material and hardness after reviewing your conditions.',
        'request' => 'Send request with this recommendation →',
        'restart' => 'Start over',
        'form_title' => 'Leave your phone — an engineer will check the choice and quote a price',
    ],
    'notes' => [
        'pu' => 'Most resistant to abrasion and load',
        'sil' => 'Withstands high temperatures',
        'epdm' => 'Resists hot water and steam',
        'nbr' => 'Resistant to oils and greases',
    ],
    'reasons' => [
        'hot' => 'Temperature :temp °C — too high for polyurethane and ordinary rubber.',
        'steam' => 'Hot water or steam present — EPDM performs reliably in that environment.',
        'oil_light' => 'Oil present, low load and abrasion — NBR is enough and economical.',
        'pu' => 'Polyurethane withstands abrasion and load far better than rubber.',
        'pu_oil' => 'Polyurethane also handles oils and greases well.',
        'food' => 'Food contact — :food_material is used.',
        'cold' => 'Low temperature (:temp °C) — a frost-resistant grade is needed.',
        'load_high' => 'Heavy load — a harder material keeps its shape.',
        'load_low' => 'Light load — a softer material grips better and runs smoothly.',
        'load_mid' => 'Medium load — general-purpose hardness.',
    ],
    'aside' => [
        'live' => 'Live recommendation',
        'answers' => 'Your answers',
        'part' => 'Part',
        'industry' => 'Industry',
        'load' => 'Load',
        'environment' => 'Environment',
        'abrasion' => 'abrasion',
    ],
    'back' => '← Back',
    'next' => 'Continue',
    'show' => 'See result',
    'aside_label' => 'Your choice',
];
