<?php

// Translation of lang/ru/factory.php (same keys).
return [
    'breadcrumb' => 'Zavod haqida',
    'hero' => [
        'lines' => [
            'Importni',
            'kutmang.',
        ],
        'accent' => 'Biz shu yerda quyamiz.',
        'lead' => 'Inter Roll — O\'zbekistondagi poliuretan va rezina detallar zavodi. Xom ashyodan tayyor detalgacha bo\'lgan butun jarayon bitta tom ostida: muhandislik, quyish, mexanik ishlov va sifat nazorati.',
        'working' => 'Zavod hozir ishlayapti',
        'hours' => 'Du–Sha · :open_hour:00–:close_hour:00',
        'shifts' => 'Smenalar: :shifts',
        'tour_caption' => 'Zavod hududi — dron fotosurati yoki 20 soniyalik video',
        'tour' => '▶ Video-sayohat',
    ],
    'stats' => [
        [
            'key' => 'years',
            'unit' => '+',
            'label' => 'yil tajriba',
        ],
        [
            'key' => 'area',
            'unit' => 'm²',
            'label' => 'ishlab chiqarish maydoni',
        ],
        [
            'key' => 'machines',
            'unit' => '',
            'label' => 'dastgoh va uskuna',
        ],
        [
            'key' => 'clients',
            'unit' => '+',
            'label' => 'doimiy sanoat mijozlari',
        ],
    ],
    'group' => [
        'eyebrow' => '[ INTER ROLL × IMPOL GROUP ]',
        'title' => 'O\'sha tajriba, o\'sha sifat — yangi kuch',
        'lead' => 'INTER ROLL turk IMPOL GROUP kompaniyasining 2008-yildan beri rivojlantirib kelayotgan ishlab chiqarish tajribasi, texnik bilimlari va sifat standartlarini O\'zbekistonda davom ettiradi.',
        'since' => '2008',
        'since_label' => 'IMPOL GROUP tashkil topgan yil',
        'vision_title' => 'Maqsadimiz',
        'vision' => 'IMPOL GROUP o\'z sifat standartlari va innovatsion yechimlarini xalqaro bozorlarga olib chiqmoqda. O\'zbekiston va Markaziy Osiyoda bu tajriba INTER ROLL brendi ostida davom etadi: mijozlarga yaqinroq, tezroq va samaraliroq xizmat bilan. Maqsadimiz — mintaqada sohaning eng ishonchli brendlaridan biri bo\'lish.',
        'mission_title' => 'Vazifamiz',
        'mission' => 'Poliuretan, rezina, silikon va sanoat mahsulotlari bo\'yicha ko\'p yillik tajribani mintaqa mijozlarining ehtiyojlari bilan birlashtirib, individual, sifatli va uzoq xizmat qiladigan yechimlar taklif qilish. Tezkor yetkazib berish, texnik yordam va sotuvdan keyingi xizmat bizni ishonchli hamkorga aylantiradi.',
        'export_title' => 'IMPOL GROUP mahsulotlarini yetkazib beradigan davlatlar',
        'countries' => ['Italiya', 'Ispaniya', 'Portugaliya', 'Tunis', 'Polsha', 'Kosovo', 'Germaniya', 'Bolgariya', 'Ruminiya', 'Fransiya'],
        'photos' => ['IMPOL GROUP zavodi, Bursa, Turkiya', 'IMPOL GROUP binosi', 'Muzokaralar xonasi', 'Hamkorlar bilan uchrashuv'],
    ],
    'tour' => [
        'eyebrow' => '[01] — Virtual sayohat',
        'title' => 'Zavod ichiga kiring',
        'hint' => 'Sex ustiga bosing →',
        'plan' => 'Zavod rejasi · sxema',
        'entrance' => 'Kirish ↓',
        'legend_flow' => '01 → 06 — material oqimi',
        'legend_selected' => '■ tanlangan sex',
        'zone' => 'Sex :n / 06',
        'prev' => 'Oldingi sex',
        'next' => 'Keyingi sex →',
        'zones' => [
            [
                'title' => 'Xom ashyo ombori',
                'photo' => 'Xom ashyo ombori — bochkalar, granulalar',
                'text' => 'Poliuretan komponentlari va rezina aralashmalari nazorat ostida, kerakli haroratda saqlanadi. Har bir partiya kirishda tekshiriladi.',
                'equipment' => [
                    'Iqlim nazoratli xona',
                    'Kiruvchi nazorat jurnali',
                    ':suppliers',
                ],
            ],
            [
                'title' => 'Muhandislik bo\'limi',
                'photo' => 'Muhandislar chizma ustida',
                'text' => 'Mijoz chizmasi yoki namunasi o\'rganiladi, material va qattiqlik tanlanadi, qolip loyihalanadi.',
                'equipment' => [
                    'CAD chizma',
                    'Namuna o\'lchash',
                    'Material tanlash',
                ],
            ],
            [
                'title' => 'Quyish sexi',
                'photo' => 'Poliuretan quyish uskunasi',
                'text' => 'Poliuretan isitilgan qoliplarga quyiladi. O\'zakli valiklarda metall o\'zak oldindan tozalanib, yopishtiruvchi qatlam surtiladi.',
                'equipment' => [
                    ':casting_machine',
                    'Isitiladigan qoliplar',
                    'Termik kamera',
                ],
            ],
            [
                'title' => 'Vulkanizatsiya',
                'photo' => 'Vulkanizatsiya pressi',
                'text' => 'Rezina detallar va rezina qoplamalar pressda bosim va harorat ostida vulkanizatsiya qilinadi.',
                'equipment' => [
                    ':press',
                    'Avtoklav',
                    'Harorat nazorati',
                ],
            ],
            [
                'title' => 'Mexanik ishlov',
                'photo' => 'Tokarlik va silliqlash dastgohlari',
                'text' => 'Quyilgan detal tokarlik va silliqlash dastgohlarida chizmadagi aniq o\'lchamga keltiriladi.',
                'equipment' => [
                    ':lathes ta tokarlik dastgohi',
                    'Silliqlash dastgohi',
                    'CNC :cnc',
                ],
            ],
            [
                'title' => 'Sifat nazorati va jo\'natish',
                'photo' => 'Laboratoriya — durometr bilan o\'lchash',
                'text' => 'Qattiqlik, o\'lcham va yopishish tekshiriladi, bayonnoma yoziladi. Detal qadoqlanib mijozga jo\'natiladi.',
                'equipment' => [
                    'Durometr (Shore A/D)',
                    'O\'lchov asboblari',
                    'Qadoqlash',
                ],
            ],
        ],
    ],
    'pillars' => [
        'eyebrow' => '[02] — Nega Inter Roll',
        'title' => 'Buyurtmachi bizdan oladigan 6 ta kafolat',
        'items' => [
            [
                'title' => 'Chizmaga 100% mos',
                'text' => 'Chizma, namuna yoki hatto eskirgan detal bo\'lsa yetarli — aynan shu o\'lchamda yasaymiz.',
                'proof' => 'O\'lchamlar bayonnomada',
            ],
            [
                'title' => 'Importdan tezroq',
                'text' => 'Chet eldan haftalab kutish o\'rniga detal mahalliy zavodda tayyorlanadi.',
                'proof' => 'Muddat: :lead_days ish kunidan',
            ],
            [
                'title' => 'Sinov partiyasi',
                'text' => 'Katta buyurtmadan oldin 1 dona namunani sinab ko\'ring. Mos kelsa — partiyani boshlaymiz.',
                'proof' => 'Minimal buyurtma: :min_order dona',
            ],
            [
                'title' => 'Kafolat',
                'text' => 'Ishlab chiqarish nuqsoni topilsa, detalni bepul qayta tayyorlaymiz.',
                'proof' => 'Kafolat: :warranty_months oy',
            ],
            [
                'title' => 'Muhandis bilan to\'g\'ridan-to\'g\'ri',
                'text' => 'Menejer orqali emas — texnik savollarni zavod muhandisi bilan o\'zbek yoki rus tilida hal qilasiz.',
                'proof' => 'Javob: :reply_hours soat ichida',
            ],
            [
                'title' => 'Hujjatlar bilan',
                'text' => 'Shartnoma, hisob-faktura, sinov bayonnomasi va kafolat taloni — korxona buxgalteriyasi uchun to\'liq to\'plam.',
                'proof' => 'QQS bilan · pul o\'tkazish',
            ],
        ],
    ],
    'compare' => [
        'eyebrow' => '[03] — Solishtiring',
        'title' => 'Importdan buyurtma vs Inter Roll',
        'criterion' => 'Mezon',
        'import' => 'Chet eldan buyurtma',
        'us' => 'Inter Roll',
        'rows' => [
            [
                'k' => 'Tayyorlash muddati',
                'a' => ':import_weeks_min–:import_weeks_max hafta + bojxona',
                'b' => ':lead_days ish kunidan',
            ],
            [
                'k' => 'Minimal partiya',
                'a' => 'Ko\'pincha :import_min_batch+ dona',
                'b' => '1 donadan (sinov namunasi)',
            ],
            [
                'k' => 'Chizmaga moslashtirish',
                'a' => 'Katalogdagi standart o\'lchamlar',
                'b' => 'Istalgan o\'lcham va qattiqlik',
            ],
            [
                'k' => 'Eski valikni qayta qoplash',
                'a' => 'Odatda yo\'q — yangisini olish kerak',
                'b' => 'Bor — o\'zakni qayta ishlatamiz',
            ],
            [
                'k' => 'To\'lov',
                'a' => 'Valyutada, oldindan',
                'b' => 'So\'mda, QQS bilan',
            ],
            [
                'k' => 'Muloqot',
                'a' => 'Xorijiy tilda, vaqt farqi bilan',
                'b' => 'O\'zbek / rus tilida, muhandis bilan',
            ],
            [
                'k' => 'Kafolat va almashtirish',
                'a' => 'Qaytarish qiyin va qimmat',
                'b' => ':warranty_months oy · bepul qayta tayyorlash',
            ],
        ],
    ],
    'quality' => [
        'eyebrow' => '[04] — Sifat nazorati',
        'title' => 'Har bir partiya — sinov bayonnomasi bilan',
        'text' => 'Detal zavoddan chiqishidan oldin qattiqligi, o\'lchamlari va yopishqoqligi tekshiriladi. Natijalar hujjatga yoziladi va detal bilan birga sizga topshiriladi.',
        'checks' => [
            'Qattiqlik — durometr bilan (Shore A)',
            'Diametr va uzunlik — chizma bo\'yicha',
            'Qoplamaning o\'zakka yopishishi',
            'Tashqi ko\'rinish va sirt sifati',
        ],
        'protocol' => 'Sinov bayonnomasi № :protocol_no',
        'part' => 'Detal',
        'part_value' => 'IR-V01 Poliuretan valik',
        'batch' => 'Partiya',
        'batch_value' => ':batch_no · :batch_qty dona',
        'date' => 'Sana',
        'rows' => [
            [
                'Qattiqlik, Shore A',
                '90 ± [_]',
            ],
            [
                'Diametr, mm',
                '120 ± [_]',
            ],
            [
                'Uzunlik, mm',
                '800 ± [_]',
            ],
            [
                'Yopishish (o\'zakka)',
                ':adhesion',
            ],
        ],
        'passed' => '✓ MOS',
        'inspector' => 'Nazoratchi: ______________',
        'accepted' => 'QABUL QILINDI',
    ],
    'result' => [
        'eyebrow' => '[05] — Natija',
        'title' => 'Oldin va keyin — o\'zingiz solishtiring',
        'text' => 'Eskirgan valikni qayta qoplash yangisini sotib olishdan arzon. Chiziqni suring va natijani ko\'ring.',
        'saving' => 'Tejash:',
        'saving_value' => ':savings_percent%',
        'term' => 'gacha · muddat :recoat_days kun',
        'before' => 'OLDIN — eskirgan valik',
        'after' => 'KEYIN — qayta qoplangan valik',
    ],
    'trust' => [
        'eyebrow' => '[06] — Bizga ishonganlar',
        'title' => 'Sanoat korxonalari bilan ishlaymiz',
        'logo' => '[Mijoz logotipi]',
        'quotes' => [
            [
                'text' => '[Mijozning haqiqiy fikri — masalan, muddat yoki sifat haqida]',
                'author' => '[Ism], [Lavozim], [Korxona]',
            ],
            [
                'text' => '[Ikkinchi mijoz fikri — qayta qoplash yoki tejash haqida]',
                'author' => '[Ism], [Lavozim], [Korxona]',
            ],
        ],
        'certificates' => [
            [
                'title' => '[Sertifikat nomi]',
                'details' => '[Raqam · amal qilish muddati]',
            ],
            [
                'title' => '[Muvofiqlik sertifikati]',
                'details' => '[Raqam · amal qilish muddati]',
            ],
            [
                'title' => '[Litsenziya / guvohnoma]',
                'details' => '[Raqam]',
            ],
        ],
    ],
    'faq' => [
        'eyebrow' => '[07] — Savollar',
        'title' => 'Buyurtmachilar ko\'p so\'raydi',
        'items' => [
            [
                'q' => 'Chizmam yo\'q, faqat eski detal bor. Bo\'ladimi?',
                'a' => 'Ha. Eski detalni yoki uning rasmini o\'lchamlari bilan yuboring — muhandisimiz o\'lchab, chizmani o\'zimiz tayyorlaymiz.',
            ],
            [
                'q' => 'Minimal buyurtma qancha?',
                'a' => 'Sinov uchun 1 dona namunadan boshlash mumkin. Seriyali buyurtmada narx kamayadi. :min_order_terms',
            ],
            [
                'q' => 'Tayyorlash qancha vaqt oladi?',
                'a' => 'Standart detallar — :lead_days ish kuni, murakkab qolipli detallar — :lead_days_complex ish kunigacha. Aniq muddat so\'rovdan keyin aytiladi.',
            ],
            [
                'q' => 'Qanday to\'lov usullari bor?',
                'a' => 'Korxonalar uchun pul o\'tkazish, QQS bilan hisob-faktura. :prepayment_terms',
            ],
            [
                'q' => 'Viloyatlarga yetkazib berasizmi?',
                'a' => ':delivery_terms',
            ],
            [
                'q' => 'Detal mos kelmasa nima bo\'ladi?',
                'a' => 'Agar nuqson ishlab chiqarishdan bo\'lsa, detalni bepul qayta tayyorlaymiz. :warranty_terms',
            ],
        ],
    ],
    'visit' => [
        'eyebrow' => '[ Eng yaxshi dalil ]',
        'title' => 'Zavodga keling — ishlab chiqarishni o\'z ko\'zingiz bilan ko\'ring',
        'text' => 'Sexlarni ko\'rsatamiz, namunalarni qo\'lingizda ushlab ko\'rasiz va muhandis bilan detalingizni joyida muhokama qilasiz.',
        'plan' => 'Tashrifni rejalashtirish →',
        'map' => 'Xaritada ochish ↗',
    ],
];
