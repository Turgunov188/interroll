<?php

// Translation of lang/ru/factory.php (same keys).
return [
    'breadcrumb' => 'Fabrika hakkında',
    'hero' => [
        'lines' => [
            'İthalatı',
            'beklemeyin.',
        ],
        'accent' => 'Burada döküyoruz.',
        'lead' => 'Inter Roll — Özbekistan\'da poliüretan ve kauçuk parça fabrikası. Hammaddeden hazır parçaya tüm süreç tek çatı altında: mühendislik, döküm, talaşlı işleme ve kalite kontrol.',
        'working' => 'Fabrika şu an çalışıyor',
        'hours' => 'Pzt–Cmt · :open_hour:00–:close_hour:00',
        'shifts' => 'Vardiya: :shifts',
        'tour_caption' => 'Fabrika alanı — dron fotoğrafı veya 20 saniyelik video',
        'tour' => '▶ Video tur',
    ],
    'stats' => [
        [
            'key' => 'years',
            'unit' => '+',
            'label' => 'yıllık deneyim',
        ],
        [
            'key' => 'area',
            'unit' => 'm²',
            'label' => 'üretim alanı',
        ],
        [
            'key' => 'machines',
            'unit' => '',
            'label' => 'tezgâh ve ekipman',
        ],
        [
            'key' => 'clients',
            'unit' => '+',
            'label' => 'düzenli endüstriyel müşteri',
        ],
    ],
    'tour' => [
        'eyebrow' => '[01] — Sanal tur',
        'title' => 'Fabrikanın içine girin',
        'hint' => 'Bir atölyeye tıklayın →',
        'plan' => 'Fabrika planı · şema',
        'entrance' => 'Giriş ↓',
        'legend_flow' => '01 → 06 — malzeme akışı',
        'legend_selected' => '■ seçili atölye',
        'zone' => 'Atölye :n / 06',
        'prev' => 'Önceki atölye',
        'next' => 'Sonraki atölye →',
        'zones' => [
            [
                'title' => 'Hammadde deposu',
                'photo' => 'Hammadde deposu — variller, granüller',
                'text' => 'Poliüretan bileşenleri ve kauçuk karışımları kontrollü olarak uygun sıcaklıkta saklanır. Her parti girişte kontrol edilir.',
                'equipment' => [
                    'İklim kontrollü oda',
                    'Giriş kontrol defteri',
                    ':suppliers',
                ],
            ],
            [
                'title' => 'Mühendislik bölümü',
                'photo' => 'Çizimler üzerinde mühendisler',
                'text' => 'Müşterinin çizimi veya numunesi incelenir, malzeme ve sertlik seçilir, kalıp tasarlanır.',
                'equipment' => [
                    'CAD çizimi',
                    'Numune ölçümü',
                    'Malzeme seçimi',
                ],
            ],
            [
                'title' => 'Döküm atölyesi',
                'photo' => 'Poliüretan döküm ekipmanı',
                'text' => 'Poliüretan ısıtılmış kalıplara dökülür. Göbekli merdanelerde metal göbek önceden temizlenir ve yapıştırıcı katman sürülür.',
                'equipment' => [
                    ':casting_machine',
                    'Isıtmalı kalıplar',
                    'Isıl kabin',
                ],
            ],
            [
                'title' => 'Vulkanizasyon',
                'photo' => 'Vulkanizasyon presi',
                'text' => 'Kauçuk parçalar ve kaplamalar preste basınç ve sıcaklık altında vulkanize edilir.',
                'equipment' => [
                    ':press',
                    'Otoklav',
                    'Sıcaklık kontrolü',
                ],
            ],
            [
                'title' => 'Talaşlı işleme',
                'photo' => 'Torna ve taşlama tezgâhları',
                'text' => 'Dökülen parça torna ve taşlama tezgâhlarında çizimdeki kesin ölçüye getirilir.',
                'equipment' => [
                    ':lathes torna tezgâhı',
                    'Taşlama tezgâhı',
                    'CNC :cnc',
                ],
            ],
            [
                'title' => 'Kalite kontrol ve sevkiyat',
                'photo' => 'Laboratuvar — durometre ile ölçüm',
                'text' => 'Sertlik, ölçüler ve yapışma kontrol edilir, rapor yazılır. Parça paketlenip müşteriye gönderilir.',
                'equipment' => [
                    'Durometre (Shore A/D)',
                    'Ölçüm cihazları',
                    'Ambalaj',
                ],
            ],
        ],
    ],
    'pillars' => [
        'eyebrow' => '[02] — Neden Inter Roll',
        'title' => 'Müşterinin bizden aldığı 6 güvence',
        'items' => [
            [
                'title' => 'Çizime %100 uygun',
                'text' => 'Çizim, numune hatta aşınmış parça yeterli — tam bu ölçüde üretiriz.',
                'proof' => 'Ölçüler raporda',
            ],
            [
                'title' => 'İthalattan hızlı',
                'text' => 'Yurt dışından haftalarca beklemek yerine parça yerli fabrikada üretilir.',
                'proof' => 'Süre: :lead_days iş gününden itibaren',
            ],
            [
                'title' => 'Deneme partisi',
                'text' => 'Büyük siparişten önce 1 numuneyi deneyin. Uyarsa partiyi başlatırız.',
                'proof' => 'Minimum sipariş: :min_order adet',
            ],
            [
                'title' => 'Garanti',
                'text' => 'Üretim kusuru bulunursa parçayı ücretsiz yeniden üretiriz.',
                'proof' => 'Garanti: :warranty_months ay',
            ],
            [
                'title' => 'Doğrudan mühendisle',
                'text' => 'Satış temsilcisi aracılığıyla değil — teknik soruları fabrika mühendisiyle Özbekçe veya Rusça çözersiniz.',
                'proof' => 'Yanıt: :reply_hours saat içinde',
            ],
            [
                'title' => 'Belgeli',
                'text' => 'Sözleşme, fatura, test raporu ve garanti belgesi — şirket muhasebesi için eksiksiz set.',
                'proof' => 'KDV\'li · havale',
            ],
        ],
    ],
    'compare' => [
        'eyebrow' => '[03] — Karşılaştırın',
        'title' => 'İthal sipariş ve Inter Roll',
        'criterion' => 'Kriter',
        'import' => 'Yurt dışından sipariş',
        'us' => 'Inter Roll',
        'rows' => [
            [
                'k' => 'Üretim süresi',
                'a' => ':import_weeks_min–:import_weeks_max hafta + gümrük',
                'b' => ':lead_days iş gününden itibaren',
            ],
            [
                'k' => 'Minimum parti',
                'a' => 'Genelde :import_min_batch+ adet',
                'b' => '1 adetten (deneme numunesi)',
            ],
            [
                'k' => 'Çizime uyarlama',
                'a' => 'Katalogdaki standart ölçüler',
                'b' => 'Her ölçü ve sertlik',
            ],
            [
                'k' => 'Eski merdaneyi yeniden kaplama',
                'a' => 'Genelde yok — yenisini almak gerekir',
                'b' => 'Var — göbeği yeniden kullanırız',
            ],
            [
                'k' => 'Ödeme',
                'a' => 'Döviz, peşin',
                'b' => 'UZS cinsinden, KDV\'li',
            ],
            [
                'k' => 'İletişim',
                'a' => 'Yabancı dilde, saat farkıyla',
                'b' => 'Özbekçe / Rusça, mühendisle',
            ],
            [
                'k' => 'Garanti ve değişim',
                'a' => 'İade zor ve pahalı',
                'b' => ':warranty_months ay · ücretsiz yeniden üretim',
            ],
        ],
    ],
    'quality' => [
        'eyebrow' => '[04] — Kalite kontrol',
        'title' => 'Her parti test raporuyla',
        'text' => 'Parça fabrikadan çıkmadan önce sertliği, ölçüleri ve yapışması kontrol edilir. Sonuçlar belgeye yazılır ve parçayla birlikte size teslim edilir.',
        'checks' => [
            'Sertlik — durometreyle (Shore A)',
            'Çap ve uzunluk — çizime göre',
            'Kaplamanın göbeğe yapışması',
            'Görünüm ve yüzey kalitesi',
        ],
        'protocol' => 'Test raporu No. :protocol_no',
        'part' => 'Parça',
        'part_value' => 'IR-V01 Poliüretan merdane',
        'batch' => 'Parti',
        'batch_value' => ':batch_no · :batch_qty adet',
        'date' => 'Tarih',
        'rows' => [
            [
                'Sertlik, Shore A',
                '90 ± [_]',
            ],
            [
                'Çap, mm',
                '120 ± [_]',
            ],
            [
                'Uzunluk, mm',
                '800 ± [_]',
            ],
            [
                'Yapışma (göbeğe)',
                ':adhesion',
            ],
        ],
        'passed' => '✓ UYGUN',
        'inspector' => 'Kontrolör: ______________',
        'accepted' => 'ONAYLANDI',
    ],
    'result' => [
        'eyebrow' => '[05] — Sonuç',
        'title' => 'Önce ve sonra — kendiniz karşılaştırın',
        'text' => 'Aşınmış merdaneyi yeniden kaplamak yenisini almaktan ucuzdur. Çizgiyi kaydırın ve sonucu görün.',
        'saving' => 'Tasarruf:',
        'saving_value' => ':savings_percent%',
        'term' => 'kadar · süre :recoat_days gün',
        'before' => 'ÖNCE — aşınmış merdane',
        'after' => 'SONRA — yeniden kaplanmış merdane',
    ],
    'trust' => [
        'eyebrow' => '[06] — Bize güvenenler',
        'title' => 'Endüstriyel şirketlerle çalışıyoruz',
        'logo' => '[Müşteri logosu]',
        'quotes' => [
            [
                'text' => '[Gerçek müşteri yorumu — örn. süre veya kalite hakkında]',
                'author' => '[Ad], [Pozisyon], [Şirket]',
            ],
            [
                'text' => '[İkinci müşteri yorumu — yeniden kaplama veya tasarruf hakkında]',
                'author' => '[Ad], [Pozisyon], [Şirket]',
            ],
        ],
        'certificates' => [
            [
                'title' => '[Sertifika adı]',
                'details' => '[Numara · geçerlilik süresi]',
            ],
            [
                'title' => '[Uygunluk sertifikası]',
                'details' => '[Numara · geçerlilik süresi]',
            ],
            [
                'title' => '[Lisans / belge]',
                'details' => '[Numara]',
            ],
        ],
    ],
    'faq' => [
        'eyebrow' => '[07] — Sorular',
        'title' => 'Müşterilerin sık sorduğu sorular',
        'items' => [
            [
                'q' => 'Çizimim yok, sadece eski parça var. Olur mu?',
                'a' => 'Evet. Eski parçayı veya ölçüleriyle fotoğrafını gönderin — mühendisimiz ölçer ve çizimi kendimiz hazırlarız.',
            ],
            [
                'q' => 'Minimum sipariş ne kadar?',
                'a' => 'Deneme için 1 numuneyle başlanabilir. Seri siparişte fiyat düşer. :min_order_terms',
            ],
            [
                'q' => 'Üretim ne kadar sürer?',
                'a' => 'Standart parçalar — :lead_days iş günü, karmaşık kalıplı parçalar — :lead_days_complex iş gününe kadar. Kesin süre talepten sonra bildirilir.',
            ],
            [
                'q' => 'Hangi ödeme yöntemleri var?',
                'a' => 'Şirketler için havale, KDV\'li fatura. :prepayment_terms',
            ],
            [
                'q' => 'Bölgelere teslimat yapıyor musunuz?',
                'a' => ':delivery_terms',
            ],
            [
                'q' => 'Parça uymazsa ne olur?',
                'a' => 'Kusur üretimden kaynaklanıyorsa parçayı ücretsiz yeniden üretiriz. :warranty_terms',
            ],
        ],
    ],
    'visit' => [
        'eyebrow' => '[ En iyi kanıt ]',
        'title' => 'Fabrikaya gelin — üretimi kendi gözlerinizle görün',
        'text' => 'Atölyeleri gösteririz, numuneleri elinizde tutar ve parçanızı mühendisle yerinde konuşursunuz.',
        'plan' => 'Ziyaret planla →',
        'map' => 'Haritada aç ↗',
    ],
];
