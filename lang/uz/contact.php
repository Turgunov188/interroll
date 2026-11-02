<?php

// Translation of lang/ru/contact.php (same keys).
return [
    'breadcrumb' => 'Aloqa',
    'title' => 'Bog\'laning —',
    'accent' => 'muhandis javob beradi',
    'status' => [
        'hours' => 'Ish vaqti: Du–Sha',
        'open' => 'Hozir ochiq',
        'closed' => 'Hozir yopiq',
        'until_close' => 'Yopilishiga :h soat :m daqiqa',
        'today' => 'Bugun',
        'tomorrow' => 'Ertaga',
        'monday' => 'Dushanba',
        'opens' => ':day :time da ochiladi · ariza qoldiring',
        'clock' => 'Toshkent vaqti: :time',
    ],
    'channels' => [
        'phone' => [
            'label' => 'Telefon',
            'text' => 'Sotuv bo\'limi · ish vaqtida darhol javob',
            'tag' => 'Eng tez',
            'action' => 'Qo\'ng\'iroq',
        ],
        'telegram' => [
            'label' => 'Telegram',
            'text' => 'Rasm va chizmalarni shu yerga yuboring',
            'tag' => 'Rasm uchun',
            'action' => 'Yozish',
        ],
        'email' => [
            'label' => 'Email',
            'text' => 'Tijoriy takliflar va hujjatlar uchun',
            'tag' => 'Hujjatlar',
            'action' => 'Yozish',
        ],
        'address' => [
            'label' => 'Manzil',
            'text' => 'Tashrifdan oldin qo\'ng\'iroq qiling',
            'tag' => 'Zavod',
            'action' => 'Xarita',
        ],
    ],
    'copy' => 'Nusxa',
    'copied' => '✓ Olindi',
    'copy_label' => ':channel nusxasini olish',
    'map' => [
        'title' => 'Qanday borish mumkin · sxema',
        'label' => 'Zavodga borish sxemasi',
        'factory' => 'INTER ROLL ZAVODI',
        'yandex' => 'Yandex Xaritada ochish ↗',
        'google' => 'Google Maps ↗',
        'route' => 'Yandex Go bilan borish ↗',
    ],
    'address' => [
        'title' => 'Manzil',
        'details' => 'Mo\'ljal: :landmark',
        'trucks' => 'Yuk mashinasi kirishi: :truck_gate',
    ],
    'hours' => [
        'title' => 'Ish vaqti',
        'weekdays' => 'Dushanba – Juma',
        'saturday' => 'Shanba',
        'sunday' => 'Yakshanba',
        'day_off' => 'Dam olish',
    ],
    'route' => [
        'title' => 'Qanday yetib kelish',
        'transit' => 'Jamoat transporti: :transit_stop, :walk_minutes daqiqa piyoda.',
        'car' => 'Avtomobilda: :landmark, bepul to\'xtash joyi :parking.',
    ],
    'people' => [
        'eyebrow' => '[ Bo\'limlar ]',
        'title' => 'Kerakli mutaxassisga to\'g\'ridan-to\'g\'ri',
        'languages' => 'Tillar: :languages',
        'items' => [
            [
                'name' => ':sales_name',
                'role' => 'Sotuv bo\'limi',
                'text' => 'Narxlar, tijoriy taklif, shartnoma va yetkazib berish.',
                'phone' => 'sales_phone',
                'languages' => 'o\'zbek, rus',
            ],
            [
                'name' => ':engineer_name',
                'role' => 'Bosh muhandis',
                'text' => 'Material tanlash, chizmalar, ish sharoiti bo\'yicha maslahat.',
                'phone' => 'engineer_phone',
                'languages' => 'o\'zbek, rus',
            ],
            [
                'name' => ':accounting_name',
                'role' => 'Buxgalteriya',
                'text' => 'Hisob-faktura, solishtirma dalolatnoma, to\'lovlar.',
                'phone' => 'accounting_phone',
                'languages' => 'o\'zbek, rus',
            ],
        ],
    ],
    'form' => [
        'eyebrow' => '[ Ariza ]',
        'title' => 'Savolingizni yozing',
        'text' => 'Ariza to\'g\'ridan-to\'g\'ri tegishli bo\'limga tushadi. Ish vaqtida :reply_minutes daqiqa ichida javob beramiz.',
        'promises' => [
            '✓ Ma\'lumotlaringiz uchinchi shaxslarga berilmaydi',
            '✓ Spam yo\'q — faqat sizning so\'rovingiz bo\'yicha',
        ],
        'topic' => 'Mavzu',
        'topics' => [
            'price' => [
                'label' => 'Narx so\'rovi',
                'hint' => 'Qaysi detal, o\'lcham va soni kerak?',
            ],
            'recoating' => [
                'label' => 'Qayta qoplash',
                'hint' => 'Valik o\'lchami va holati haqida yozing',
            ],
            'technical' => [
                'label' => 'Texnik savol',
                'hint' => 'Ish sharoiti: harorat, yuklama, muhit',
            ],
            'partnership' => [
                'label' => 'Hamkorlik',
                'hint' => 'Taklifingizni qisqacha yozing',
            ],
        ],
        'reply' => 'Qanday javob beraylik?',
        'replies' => [
            'phone' => 'Qo\'ng\'iroq',
            'telegram' => 'Telegram',
            'email' => 'Email',
        ],
        'sent_title' => 'Ariza qabul qilindi',
        'sent_text' => 'Mavzu: :topic. :reply — shu usulda bog\'lanamiz.',
        'again' => 'Yana ariza yuborish',
    ],
    'requisites' => [
        'eyebrow' => '[ Korxonalar uchun ]',
        'title' => 'Rekvizitlar',
        'text' => 'Shartnoma va to\'lov uchun. Bir tugma bilan nusxa oling.',
        'rows' => [
            [
                'Nomi',
                'OOO «Inter Roll»',
            ],
            [
                'STIR (INN)',
                ':inn',
            ],
            [
                'Bank',
                ':bank',
            ],
            [
                'H/r',
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
                'Yuridik manzil',
                ':legal_address',
            ],
        ],
        'copy' => 'Rekvizitlarni nusxalash',
        'copied' => '✓ Nusxa olindi',
    ],
];
