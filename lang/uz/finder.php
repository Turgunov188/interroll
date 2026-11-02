<?php

// Translation of lang/ru/finder.php (same keys).
return [
    'eyebrow' => '[ Detal tanlash ustasi ]',
    'title' => 'Qaysi detal kerakligini',
    'accent' => 'bilmaysizmi?',
    'lead' => '4 ta savolga javob bering — ish sharoitingizga mos material va qattiqlikni tavsiya qilamiz. 1 daqiqa.',
    'step' => 'Qadam :n / 05',
    'progress' => [
        'Detal turi',
        'Soha',
        'Ish sharoiti',
        'Muhit',
        'Natija',
    ],
    'type' => [
        'title' => 'Sizga qanday detal kerak?',
        'options' => [
            'roller' => [
                'title' => 'Valik / rolik',
                'text' => 'Konveyer, press, poligrafiya',
            ],
            'wheel' => [
                'title' => 'G\'ildirak',
                'text' => 'Arava, yuk ko\'targich',
            ],
            'plate' => [
                'title' => 'Plastina / qirg\'ich',
                'text' => 'Tozalash, qirish',
            ],
            'coupling' => [
                'title' => 'Mufta / prujina',
                'text' => 'Uzatma, shtamp',
            ],
            'unknown' => [
                'title' => 'Aniq bilmayman',
                'text' => 'Rasm yoki namuna bor',
            ],
        ],
    ],
    'industry' => [
        'title' => 'Qaysi sohada ishlaydi?',
        'options' => [
            'metal' => [
                'title' => 'Metallurgiya',
                'text' => 'Prokat, quyish',
            ],
            'print' => [
                'title' => 'Poligrafiya',
                'text' => 'Bo\'yoq, qog\'oz, laminatsiya',
            ],
            'food' => [
                'title' => 'Oziq-ovqat',
                'text' => 'Qadoqlash, konveyer',
            ],
            'auto' => [
                'title' => 'Avtomobil',
                'text' => 'Osma, tayanchlar',
            ],
            'logistics' => [
                'title' => 'Ombor va logistika',
                'text' => 'Arava, rolganglar',
            ],
            'other' => [
                'title' => 'Boshqa',
                'text' => 'Yog\'och, qurilish va b.',
            ],
        ],
    ],
    'conditions' => [
        'title' => 'Detal qanday ishlaydi?',
        'load' => 'Yuklama / bosim',
        'loads' => [
            'low' => [
                'title' => 'Yengil',
                'text' => 'ushlash, yo\'naltirish',
            ],
            'mid' => [
                'title' => 'O\'rtacha',
                'text' => 'konveyer, tortish',
            ],
            'high' => [
                'title' => 'Katta',
                'text' => 'press, prokat',
            ],
        ],
        'abrasion' => 'Ishqalanish / yeyilish',
        'abrasions' => [
            'low' => [
                'title' => 'Kam',
                'text' => 'silliq yuza',
            ],
            'mid' => [
                'title' => 'O\'rtacha',
                'text' => 'oddiy material',
            ],
            'high' => [
                'title' => 'Ko\'p',
                'text' => 'qum, metall, abraziv',
            ],
        ],
    ],
    'environment' => [
        'title' => 'Ish muhiti qanday?',
        'temperature' => 'Ishchi harorat',
        'options' => [
            'oil' => [
                'title' => 'Yog\' / moy',
                'text' => 'Moy, benzin, solyarka bilan aloqa',
                'short' => 'yog\'',
            ],
            'water' => [
                'title' => 'Suv / bug\'',
                'text' => 'Yuvish, namlik, bug\'',
                'short' => 'suv',
            ],
            'food' => [
                'title' => 'Oziq-ovqat',
                'text' => 'Mahsulot bilan to\'g\'ridan-to\'g\'ri aloqa',
                'short' => 'oziq-ovqat',
            ],
        ],
    ],
    'result' => [
        'label' => 'Tavsiya',
        'material' => 'Material',
        'hardness' => 'Qattiqlik',
        'why' => 'Nega aynan shu?',
        'products' => 'Mos mahsulotlar',
        'disclaimer' => 'Bu umumiy tavsiya. Yakuniy material va qattiqlikni zavod muhandisi sizning sharoitingizni ko\'rib tasdiqlaydi.',
        'request' => 'Shu tavsiya bilan so\'rov yuborish →',
        'restart' => 'Qaytadan boshlash',
        'form_title' => 'Telefon qoldiring — muhandis tanlovni tekshirib, narxni aytadi',
    ],
    'notes' => [
        'pu' => 'Ishqalanish va yuklamaga eng chidamli',
        'sil' => 'Yuqori haroratga chidamli',
        'epdm' => 'Issiq suv va bug\'ga chidamli',
        'nbr' => 'Yog\' va moylarga chidamli',
    ],
    'reasons' => [
        'hot' => 'Harorat :temp °C — poliuretan va oddiy rezina uchun juda yuqori.',
        'steam' => 'Issiq suv yoki bug\' bor — EPDM bunday muhitda barqaror ishlaydi.',
        'oil_light' => 'Yog\' bor, yuklama va ishqalanish past — NBR yetarli va iqtisodiy.',
        'pu' => 'Ishqalanish va yuklamaga poliuretan rezinadan ancha chidamli.',
        'pu_oil' => 'Poliuretan yog\' va moylarga ham yaxshi chidaydi.',
        'food' => 'Oziq-ovqat bilan aloqa — :food_material tanlanadi.',
        'cold' => 'Past harorat (:temp °C) — sovuqqa chidamli marka kerak.',
        'load_high' => 'Katta yuklama — qattiqroq material shaklini saqlaydi.',
        'load_low' => 'Yengil yuklama — yumshoqroq material yaxshiroq ushlaydi va silliq ishlaydi.',
        'load_mid' => 'O\'rtacha yuklama — universal qattiqlik.',
    ],
    'aside' => [
        'live' => 'Jonli tavsiya',
        'answers' => 'Javoblaringiz',
        'part' => 'Detal',
        'industry' => 'Soha',
        'load' => 'Yuklama',
        'environment' => 'Muhit',
        'abrasion' => 'ishqalanish',
    ],
    'back' => '← Orqaga',
    'next' => 'Davom etish',
    'show' => 'Natijani ko\'rish',
    'aside_label' => 'Tanlovingiz',
];
