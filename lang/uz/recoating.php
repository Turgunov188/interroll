<?php

// Translation of lang/ru/recoating.php (same keys).
return [
    'breadcrumb_services' => 'Xizmatlar',
    'breadcrumb' => 'Qayta qoplash',
    'hero' => [
        'lines' => [
            'Eski valikni',
            'tashlamang.',
        ],
        'accent' => 'Qayta qoplaymiz.',
        'lead' => 'Eskirgan qoplamani olib tashlaymiz, metall o\'zakni tiklaymiz va ustiga yangi poliuretan yoki rezina quyamiz. Valik yangidek ishlaydi — yangisini sotib olishdan arzon va tezroq.',
        'send_photo' => 'Valik rasmini yuborish →',
        'savings' => 'Tejashni hisoblash',
    ],
    'process' => [
        'title' => 'Jarayon · jonli sxema',
        'label' => 'Qayta qoplash jarayoni: :stage',
        'core' => 'O\'ZAK — QAYTA ISHLATILADI',
        'worn' => 'ESKI QOPLAMA — YEYILGAN',
        'stripped' => 'QOPLAMA OLIB TASHLANDI',
        'new_layer' => ':material — YANGI QATLAM',
        'stages' => [
            'Eskirgan valik',
            'Qoplamani olish',
            'Yangi qatlam quyish',
            'Silliqlash va nazorat',
        ],
    ],
    'calculator' => [
        'eyebrow' => '[01] — Tejash kalkulyatori',
        'title' => 'Qancha tejashingizni hisoblang',
        'price' => 'Yangi valik narxi (1 dona), so\'m',
        'quantity' => 'Valiklar soni',
        'ratio' => 'Qayta qoplash narxi, yangisiga nisbatan',
        'note' => 'Taxminiy hisob. Aniq narx valik holati, o\'lchami va materialiga bog\'liq — rasmini yuboring, aniq hisoblab beramiz.',
        'new' => 'Yangi valiklar',
        'recoated' => 'Qayta qoplash',
        'currency' => 'so\'m',
        'saving' => 'Sizning tejashingiz',
        'cheaper' => 'so\'m · :percent% kam',
        'action' => 'Aniq narxni bilish →',
    ],
    'steps' => [
        'eyebrow' => '[02] — Qanday ishlaymiz',
        'title' => 'Valik qabul qilinganidan qaytarilgunicha',
        'items' => [
            [
                'title' => 'Qabul qilish',
                'text' => 'Valik zavodga keltiriladi yoki biz olib ketamiz.',
                'time' => '1 kun',
            ],
            [
                'title' => 'Diagnostika',
                'text' => 'O\'zak va podshipnik joylari tekshiriladi, o\'lchanadi.',
                'time' => ':recoat_diag_hours soat',
            ],
            [
                'title' => 'Eskisini olish',
                'text' => 'Eski qoplama mexanik yoki termik usulda olib tashlanadi.',
                'time' => ':recoat_strip_hours soat',
            ],
            [
                'title' => 'O\'zakni tayyorlash',
                'text' => 'Tozalash, qumlash, yopishtiruvchi qatlam surtish.',
                'time' => ':recoat_prep_hours soat',
            ],
            [
                'title' => 'Quyish',
                'text' => 'Yangi poliuretan yoki rezina quyiladi va qotiriladi.',
                'time' => ':recoat_cast_days kun',
            ],
            [
                'title' => 'Silliqlash va QC',
                'text' => 'Aniq o\'lchamga silliqlanadi, qattiqlik o\'lchanadi.',
                'time' => ':recoat_finish_hours soat',
            ],
        ],
    ],
    'materials' => [
        'eyebrow' => '[03] — Qoplama materiali',
        'title' => 'Ish sharoitiga mos materialni tanlang',
        'note' => 'Umumiy taqqoslash. Muhandis sizning sharoitingizga qarab aniq tavsiya beradi.',
        'items' => [
            'pu' => [
                'hardness' => '70–95 A',
                'text' => 'Ishqalanish va yuklamaga eng chidamli. Konveyer, press va prokat valiklari uchun.',
                'use' => 'Konveyer · press · prokat',
            ],
            'nbr' => [
                'hardness' => '60–85 A',
                'text' => 'Yog\' va moylarga chidamli. Poligrafiya va moyli muhitdagi valiklar uchun.',
                'use' => 'Poligrafiya · moyli muhit',
            ],
            'epdm' => [
                'hardness' => '50–80 A',
                'text' => 'Issiqlik, suv, bug\' va ob-havoga chidamli. Yuvish va issiq muhit uchun.',
                'use' => 'Yuvish · bug\' · tashqi muhit',
            ],
            'sil' => [
                'hardness' => '30–80 A',
                'text' => 'Yuqori haroratga chidamli, yopishmaydi. Laminatsiya va termik ishlov uchun.',
                'use' => 'Laminatsiya · issiq presslash',
            ],
        ],
    ],
    'result' => [
        'eyebrow' => '[04] — Natija',
        'title' => 'Oldin va keyin',
        'text' => 'Chiziqni surib, eskirgan valni qayta qoplangandan keyingi holati bilan solishtiring.',
        'cases' => [
            'Konveyer valigi',
            'Poligrafiya valigi',
            'Press valigi',
        ],
        'before' => 'OLDIN — :case',
        'after' => 'KEYIN — :case',
    ],
    'shipping' => [
        'eyebrow' => '[05] — Viloyatdanmisiz?',
        'title' => 'Valikni zavodga yuborish — 3 qadam',
        'step' => 'QADAM :n',
        'items' => [
            [
                'title' => 'Rasm va o\'lcham yuboring',
                'text' => 'Telegram yoki forma orqali valik rasmi, diametri va uzunligini yuboring.',
            ],
            [
                'title' => 'Valikni jo\'nating',
                'text' => ':carrier orqali yoki o\'zingiz olib keling. Toshkent bo\'ylab olib ketish — :pickup_terms.',
            ],
            [
                'title' => 'Tayyor valikni oling',
                'text' => 'Sinov bayonnomasi va kafolat taloni bilan qaytaramiz.',
            ],
        ],
    ],
    'request' => [
        'eyebrow' => '[ Ariza ]',
        'title' => 'Valik rasmini yuboring — narx va muddatni aytamiz',
        'text' => 'Rasm va taxminiy o\'lchamlar yetarli. Muhandis :reply_hours soat ichida bog\'lanadi.',
        'name' => 'Ism / korxona',
        'diameter' => 'Diametr, mm',
        'length' => 'Uzunlik, mm',
        'quantity' => 'Soni',
        'material' => 'Tanlangan material',
        'photo' => 'Valik rasmi',
    ],
    'faq' => [
        'title' => 'Qayta qoplash bo\'yicha savollar',
        'items' => [
            [
                'q' => 'Har qanday valikni qayta qoplash mumkinmi?',
                'a' => 'Agar metall o\'zak egilmagan va jiddiy shikastlanmagan bo\'lsa — ha. Diagnostikadan keyin aniq aytamiz.',
            ],
            [
                'q' => 'Necha marta qayta qoplash mumkin?',
                'a' => 'O\'zak holati yaxshi bo\'lsa, bir necha marta. :recoat_times',
            ],
            [
                'q' => 'Qayta qoplangan valik yangisidan yomon emasmi?',
                'a' => 'Yangi qatlam xuddi yangi valikdagidek quyiladi va bir xil nazoratdan o\'tadi. :warranty_terms',
            ],
            [
                'q' => 'Materialni o\'zgartirsa bo\'ladimi?',
                'a' => 'Ha. Masalan, rezina qoplamani yanada chidamli poliuretanga almashtirish mumkin — muhandis tavsiya beradi.',
            ],
        ],
    ],
];
