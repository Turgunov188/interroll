<?php

// Translation of lang/ru/contact.php (same keys).
return [
    'breadcrumb' => 'İletişim',
    'title' => 'Bize ulaşın —',
    'accent' => 'mühendis yanıtlar',
    'status' => [
        'hours' => 'Çalışma saatleri: Pzt–Cmt',
        'open' => 'Şu an açık',
        'closed' => 'Şu an kapalı',
        'until_close' => 'Kapanışa :h sa :m dk',
        'today' => 'Bugün',
        'tomorrow' => 'Yarın',
        'monday' => 'Pazartesi',
        'opens' => ':day :time — açılıyoruz · talep bırakın',
        'clock' => 'Taşkent saati: :time',
    ],
    'channels' => [
        'phone' => [
            'label' => 'Telefon',
            'text' => 'Satış departmanı · mesai saatlerinde anında yanıt',
            'tag' => 'En hızlı',
            'action' => 'Arama',
        ],
        'telegram' => [
            'label' => 'Telegram',
            'text' => 'Fotoğraf ve çizimleri buraya gönderin',
            'tag' => 'Fotoğraflar için',
            'action' => 'Yaz',
        ],
        'email' => [
            'label' => 'Email',
            'text' => 'Teklifler ve belgeler için',
            'tag' => 'Belgeler',
            'action' => 'Yaz',
        ],
        'address' => [
            'label' => 'Adres',
            'text' => 'Ziyaretten önce arayın',
            'tag' => 'Fabrika',
            'action' => 'Harita',
        ],
    ],
    'copy' => 'Kopyala',
    'copied' => '✓ Alındı',
    'copy_label' => ':channel kopyala',
    'map' => [
        'title' => 'Nasıl gelinir · kroki',
        'label' => 'Fabrikaya ulaşım krokisi',
        'factory' => 'INTER ROLL FABRİKASI',
        'yandex' => 'Yandex Haritalar\'da aç ↗',
        'google' => 'Google Maps ↗',
        'route' => 'Yandex Go ile git ↗',
    ],
    'address' => [
        'title' => 'Adres',
        'details' => 'İşaret noktası: :landmark',
        'trucks' => 'Kamyon girişi: :truck_gate',
    ],
    'hours' => [
        'title' => 'Çalışma saatleri',
        'weekdays' => 'Pazartesi – Cuma',
        'saturday' => 'Cumartesi',
        'sunday' => 'Pazar',
        'day_off' => 'Kapalı',
    ],
    'route' => [
        'title' => 'Nasıl gelinir',
        'transit' => 'Toplu taşıma: :transit_stop, :walk_minutes dakika yürüme.',
        'car' => 'Arabayla: :landmark, ücretsiz otopark :parking.',
    ],
    'people' => [
        'eyebrow' => '[ Departmanlar ]',
        'title' => 'Doğrudan doğru uzmana',
        'languages' => 'Diller: :languages',
        'items' => [
            [
                'name' => ':sales_name',
                'role' => 'Satış departmanı',
                'text' => 'Fiyatlar, teklif, sözleşme ve teslimat.',
                'phone' => 'sales_phone',
                'languages' => 'Özbekçe, Rusça',
            ],
            [
                'name' => ':engineer_name',
                'role' => 'Baş mühendis',
                'text' => 'Malzeme seçimi, çizimler, çalışma koşulları hakkında danışmanlık.',
                'phone' => 'engineer_phone',
                'languages' => 'Özbekçe, Rusça',
            ],
            [
                'name' => ':accounting_name',
                'role' => 'Muhasebe',
                'text' => 'Faturalar, mutabakat belgeleri, ödemeler.',
                'phone' => 'accounting_phone',
                'languages' => 'Özbekçe, Rusça',
            ],
        ],
    ],
    'form' => [
        'eyebrow' => '[ Talep ]',
        'title' => 'Sorunuzu yazın',
        'text' => 'Talebiniz doğrudan ilgili departmana gider. Mesai saatlerinde :reply_minutes dakika içinde yanıt veririz.',
        'promises' => [
            '✓ Bilgileriniz üçüncü kişilerle paylaşılmaz',
            '✓ Spam yok — sadece talebinizle ilgili',
        ],
        'topic' => 'Konu',
        'topics' => [
            'price' => [
                'label' => 'Fiyat talebi',
                'hint' => 'Hangi parça, ölçü ve adet gerekiyor?',
            ],
            'recoating' => [
                'label' => 'Yeniden kaplama',
                'hint' => 'Merdanenin ölçüsünü ve durumunu yazın',
            ],
            'technical' => [
                'label' => 'Teknik soru',
                'hint' => 'Çalışma koşulları: sıcaklık, yük, ortam',
            ],
            'partnership' => [
                'label' => 'İş birliği',
                'hint' => 'Teklifinizi kısaca yazın',
            ],
        ],
        'reply' => 'Size nasıl dönelim?',
        'replies' => [
            'phone' => 'Arama',
            'telegram' => 'Telegram',
            'email' => 'Email',
        ],
        'sent_title' => 'Talep alındı',
        'sent_text' => 'Konu: :topic. :reply — bu yolla size ulaşacağız.',
        'again' => 'Yeni talep gönder',
    ],
    'requisites' => [
        'eyebrow' => '[ Şirketler için ]',
        'title' => 'Şirket bilgileri',
        'text' => 'Sözleşme ve ödeme için. Tek tıkla kopyalayın.',
        'rows' => [
            [
                'Adı',
                'Inter Roll LLC',
            ],
            [
                'Vergi no (STIR)',
                ':inn',
            ],
            [
                'Banka',
                ':bank',
            ],
            [
                'Hesap no',
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
                'Yasal adres',
                ':legal_address',
            ],
        ],
        'copy' => 'Şirket bilgilerini kopyala',
        'copied' => '✓ Kopyalandı',
    ],
];
