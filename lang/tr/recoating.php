<?php

// Translation of lang/ru/recoating.php (same keys).
return [
    'breadcrumb_services' => 'Hizmetler',
    'breadcrumb' => 'Yeniden kaplama',
    'hero' => [
        'lines' => [
            'Eski merdaneyi',
            'atmayın.',
        ],
        'accent' => 'Yeniden kaplarız.',
        'lead' => 'Aşınmış kaplamayı söker, metal göbeği onarır ve üzerine yeni poliüretan veya kauçuk dökeriz. Merdane yeni gibi çalışır — yenisini almaktan ucuz ve hızlı.',
        'send_photo' => 'Merdane fotoğrafı gönder →',
        'savings' => 'Tasarruf hesabı',
    ],
    'process' => [
        'title' => 'Süreç · canlı şema',
        'label' => 'Yeniden kaplama süreci: :stage',
        'core' => 'GÖBEK — YENİDEN KULLANILIR',
        'worn' => 'ESKİ KAPLAMA — AŞINMIŞ',
        'stripped' => 'KAPLAMA SÖKÜLDÜ',
        'new_layer' => ':material — YENİ KATMAN',
        'stages' => [
            'Aşınmış merdane',
            'Kaplama sökme',
            'Yeni katman dökümü',
            'Taşlama ve kontrol',
        ],
    ],
    'calculator' => [
        'eyebrow' => '[01] — Tasarruf hesaplayıcı',
        'title' => 'Tasarrufunuzu hesaplayın',
        'price' => 'Yeni merdane fiyatı (1 adet), UZS',
        'quantity' => 'Merdane sayısı',
        'ratio' => 'Yenisine göre yeniden kaplama fiyatı',
        'note' => 'Tahmini hesap. Kesin fiyat merdanenin durumuna, ölçüsüne ve malzemesine bağlıdır — fotoğraf gönderin, kesin hesaplayalım.',
        'new' => 'Yeni merdaneler',
        'recoated' => 'Yeniden kaplama',
        'currency' => 'UZS',
        'saving' => 'Tasarrufunuz',
        'cheaper' => 'UZS · :percent% daha ucuz',
        'action' => 'Kesin fiyatı öğren →',
    ],
    'steps' => [
        'eyebrow' => '[02] — Nasıl çalışıyoruz',
        'title' => 'Merdanenin kabulünden iadesine kadar',
        'items' => [
            [
                'title' => 'Kabul',
                'text' => 'Merdane fabrikaya getirilir veya biz alırız.',
                'time' => '1 gün',
            ],
            [
                'title' => 'Teşhis',
                'text' => 'Göbek ve rulman yuvaları kontrol edilir, ölçülür.',
                'time' => ':recoat_diag_hours saat',
            ],
            [
                'title' => 'Eskisini sökme',
                'text' => 'Eski kaplama mekanik veya termal yöntemle sökülür.',
                'time' => ':recoat_strip_hours saat',
            ],
            [
                'title' => 'Göbek hazırlığı',
                'text' => 'Temizlik, kumlama, yapıştırıcı katman sürme.',
                'time' => ':recoat_prep_hours saat',
            ],
            [
                'title' => 'Döküm',
                'text' => 'Yeni poliüretan veya kauçuk dökülür ve kürlenir.',
                'time' => ':recoat_cast_days gün',
            ],
            [
                'title' => 'Taşlama ve KK',
                'text' => 'Tam ölçüye taşlanır, sertlik ölçülür.',
                'time' => ':recoat_finish_hours saat',
            ],
        ],
    ],
    'materials' => [
        'eyebrow' => '[03] — Kaplama malzemesi',
        'title' => 'Çalışma koşullarına uygun malzemeyi seçin',
        'note' => 'Genel karşılaştırma. Mühendis koşullarınıza göre kesin öneri verir.',
        'items' => [
            'pu' => [
                'hardness' => '70–95 A',
                'text' => 'Aşınma ve yüke en dayanıklı. Konveyör, pres ve haddeleme merdaneleri için.',
                'use' => 'Konveyör · pres · haddeleme',
            ],
            'nbr' => [
                'hardness' => '60–85 A',
                'text' => 'Yağ ve greslere dayanıklı. Matbaa ve yağlı ortam merdaneleri için.',
                'use' => 'Matbaa · yağlı ortam',
            ],
            'epdm' => [
                'hardness' => '50–80 A',
                'text' => 'Isıya, suya, buhara ve hava koşullarına dayanıklı. Yıkama ve sıcak ortamlar için.',
                'use' => 'Yıkama · buhar · dış ortam',
            ],
            'sil' => [
                'hardness' => '30–80 A',
                'text' => 'Yüksek sıcaklığa dayanıklı, yapışmaz. Laminasyon ve ısıl işlem için.',
                'use' => 'Laminasyon · sıcak presleme',
            ],
        ],
    ],
    'result' => [
        'eyebrow' => '[04] — Sonuç',
        'title' => 'Önce ve sonra',
        'text' => 'Çizgiyi kaydırın: aşınmış merdaneyi yeniden kaplanmış haliyle karşılaştırın.',
        'cases' => [
            'Konveyör merdanesi',
            'Matbaa merdanesi',
            'Pres merdanesi',
        ],
        'before' => 'ÖNCE — :case',
        'after' => 'SONRA — :case',
    ],
    'shipping' => [
        'eyebrow' => '[05] — Bölgeden misiniz?',
        'title' => 'Merdaneyi fabrikaya gönderme — 3 adım',
        'step' => 'ADIM :n',
        'items' => [
            [
                'title' => 'Fotoğraf ve ölçü gönderin',
                'text' => 'Telegram veya form üzerinden merdane fotoğrafı, çapı ve uzunluğunu gönderin.',
            ],
            [
                'title' => 'Merdaneyi gönderin',
                'text' => ':carrier ile veya kendiniz getirin. Taşkent içi teslim alma — :pickup_terms.',
            ],
            [
                'title' => 'Hazır merdaneyi teslim alın',
                'text' => 'Test raporu ve garanti belgesiyle iade ederiz.',
            ],
        ],
    ],
    'request' => [
        'eyebrow' => '[ Talep ]',
        'title' => 'Merdane fotoğrafı gönderin — fiyat ve süreyi söyleyelim',
        'text' => 'Fotoğraf ve yaklaşık ölçüler yeterli. Mühendis :reply_hours saat içinde döner.',
        'name' => 'Ad / şirket',
        'diameter' => 'Çap, mm',
        'length' => 'Uzunluk, mm',
        'quantity' => 'Adet',
        'material' => 'Seçilen malzeme',
        'photo' => 'Merdane fotoğrafı',
    ],
    'faq' => [
        'title' => 'Yeniden kaplama hakkında sorular',
        'items' => [
            [
                'q' => 'Her merdane yeniden kaplanabilir mi?',
                'a' => 'Metal göbek eğilmemiş ve ciddi hasar görmemişse — evet. Teşhisten sonra kesin söyleriz.',
            ],
            [
                'q' => 'Kaç kez yeniden kaplanabilir?',
                'a' => 'Göbek iyi durumdaysa birkaç kez. :recoat_times',
            ],
            [
                'q' => 'Yeniden kaplanmış merdane yenisinden kötü mü?',
                'a' => 'Yeni katman yeni merdanedeki gibi dökülür ve aynı kontrolden geçer. :warranty_terms',
            ],
            [
                'q' => 'Malzeme değiştirilebilir mi?',
                'a' => 'Evet. Örneğin kauçuk kaplama daha dayanıklı poliüretanla değiştirilebilir — mühendis önerir.',
            ],
        ],
    ],
];
