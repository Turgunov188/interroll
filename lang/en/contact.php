<?php

// Translation of lang/ru/contact.php (same keys).
return [
    'breadcrumb' => 'Contact',
    'title' => 'Get in touch —',
    'accent' => 'an engineer replies',
    'status' => [
        'hours' => 'Working hours: Mon–Sat',
        'open' => 'Open now',
        'closed' => 'Closed now',
        'until_close' => 'Closes in :h h :m min',
        'today' => 'Today',
        'tomorrow' => 'Tomorrow',
        'monday' => 'Monday',
        'opens' => ':day :time — we open · leave a request',
        'clock' => 'Tashkent time: :time',
    ],
    'channels' => [
        'phone' => [
            'label' => 'Phone',
            'text' => 'Sales department · instant reply during working hours',
            'tag' => 'Fastest',
            'action' => 'Call',
        ],
        'telegram' => [
            'label' => 'Telegram',
            'text' => 'Send photos and drawings here',
            'tag' => 'For photos',
            'action' => 'Write',
        ],
        'email' => [
            'label' => 'Email',
            'text' => 'For commercial offers and documents',
            'tag' => 'Documents',
            'action' => 'Write',
        ],
        'address' => [
            'label' => 'Address',
            'text' => 'Call before visiting',
            'tag' => 'Factory',
            'action' => 'Map',
        ],
    ],
    'copy' => 'Copy',
    'copied' => '✓ Received',
    'copy_label' => 'Copy :channel',
    'map' => [
        'title' => 'How to get here · map',
        'label' => 'Directions to the factory',
        'factory' => 'INTER ROLL FACTORY',
        'yandex' => 'Open in Yandex Maps ↗',
        'google' => 'Google Maps ↗',
        'route' => 'Ride with Yandex Go ↗',
    ],
    'address' => [
        'title' => 'Address',
        'details' => 'Landmark: :landmark',
        'trucks' => 'Truck entrance: :truck_gate',
    ],
    'hours' => [
        'title' => 'Working hours',
        'weekdays' => 'Monday – Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
        'day_off' => 'Day off',
    ],
    'route' => [
        'title' => 'How to get here',
        'transit' => 'Public transport: :transit_stop, :walk_minutes min walk.',
        'car' => 'By car: :landmark, free parking :parking.',
    ],
    'people' => [
        'eyebrow' => '[ Departments ]',
        'title' => 'Straight to the right specialist',
        'languages' => 'Languages: :languages',
        'items' => [
            [
                'name' => ':sales_name',
                'role' => 'Sales department',
                'text' => 'Prices, commercial offers, contracts and delivery.',
                'phone' => 'sales_phone',
                'languages' => 'Uzbek, Russian',
            ],
            [
                'name' => ':engineer_name',
                'role' => 'Chief engineer',
                'text' => 'Material selection, drawings, advice on operating conditions.',
                'phone' => 'engineer_phone',
                'languages' => 'Uzbek, Russian',
            ],
            [
                'name' => ':accounting_name',
                'role' => 'Accounting',
                'text' => 'Invoices, reconciliation statements, payments.',
                'phone' => 'accounting_phone',
                'languages' => 'Uzbek, Russian',
            ],
        ],
    ],
    'form' => [
        'eyebrow' => '[ Request ]',
        'title' => 'Write your question',
        'text' => 'Your request goes straight to the right department. During working hours we reply within :reply_minutes minutes.',
        'promises' => [
            '✓ Your data is never shared with third parties',
            '✓ No spam — only about your request',
        ],
        'topic' => 'Subject',
        'topics' => [
            'price' => [
                'label' => 'Price request',
                'hint' => 'Which part, size and quantity do you need?',
            ],
            'recoating' => [
                'label' => 'Recoating',
                'hint' => 'Describe the roller\'s size and condition',
            ],
            'technical' => [
                'label' => 'Technical question',
                'hint' => 'Operating conditions: temperature, load, environment',
            ],
            'partnership' => [
                'label' => 'Partnership',
                'hint' => 'Briefly describe your proposal',
            ],
        ],
        'reply' => 'How should we reply?',
        'replies' => [
            'phone' => 'Call',
            'telegram' => 'Telegram',
            'email' => 'Email',
        ],
        'sent_title' => 'Request received',
        'sent_text' => 'Subject: :topic. :reply — we will contact you this way.',
        'again' => 'Send another request',
    ],
    'requisites' => [
        'eyebrow' => '[ For companies ]',
        'title' => 'Company details',
        'text' => 'For contracts and payment. Copy with one click.',
        'rows' => [
            [
                'Name',
                'Inter Roll LLC',
            ],
            [
                'TIN',
                ':inn',
            ],
            [
                'Bank',
                ':bank',
            ],
            [
                'Account',
                ':account',
            ],
            [
                'MFO',
                ':mfo',
            ],
            [
                'OKED',
                ':oked',
            ],
            [
                'Legal address',
                ':legal_address',
            ],
        ],
        'copy' => 'Copy company details',
        'copied' => '✓ Copied',
    ],
];
