<?php

// Translation of lang/ru/finder.php (same keys).
return [
    'eyebrow' => '[ Parça seçim sihirbazı ]',
    'title' => 'Hangi parçanın',
    'accent' => 'gerektiğini bilmiyor musunuz?',
    'lead' => '4 soruyu yanıtlayın — koşullarınıza uygun malzeme ve sertliği önerelim. 1 dakika.',
    'step' => 'Adım :n / 05',
    'progress' => [
        'Parça türü',
        'Sektör',
        'Çalışma koşulları',
        'Ortam',
        'Sonuç',
    ],
    'type' => [
        'title' => 'Hangi parçaya ihtiyacınız var?',
        'options' => [
            'roller' => [
                'title' => 'Merdane / makara',
                'text' => 'Konveyör, pres, matbaa',
            ],
            'wheel' => [
                'title' => 'Tekerlek',
                'text' => 'Arabalar, forkliftler',
            ],
            'plate' => [
                'title' => 'Plaka / sıyırıcı',
                'text' => 'Temizlik, sıyırma',
            ],
            'coupling' => [
                'title' => 'Kaplin / yay',
                'text' => 'Aktarma, kalıp',
            ],
            'unknown' => [
                'title' => 'Emin değilim',
                'text' => 'Fotoğraf veya numune var',
            ],
        ],
    ],
    'industry' => [
        'title' => 'Hangi sektörde çalışıyor?',
        'options' => [
            'metal' => [
                'title' => 'Metalurji',
                'text' => 'Haddeleme, döküm',
            ],
            'print' => [
                'title' => 'Matbaa',
                'text' => 'Boya, kâğıt, laminasyon',
            ],
            'food' => [
                'title' => 'Gıda',
                'text' => 'Paketleme, konveyör',
            ],
            'auto' => [
                'title' => 'Otomotiv',
                'text' => 'Süspansiyon, takozlar',
            ],
            'logistics' => [
                'title' => 'Depo ve lojistik',
                'text' => 'Arabalar, makaralı konveyörler',
            ],
            'other' => [
                'title' => 'Diğer',
                'text' => 'Ahşap, inşaat vb.',
            ],
        ],
    ],
    'conditions' => [
        'title' => 'Parça nasıl çalışıyor?',
        'load' => 'Yük / basınç',
        'loads' => [
            'low' => [
                'title' => 'Hafif',
                'text' => 'tutuş, yönlendirme',
            ],
            'mid' => [
                'title' => 'Orta',
                'text' => 'konveyör, çekme',
            ],
            'high' => [
                'title' => 'Yüksek',
                'text' => 'pres, haddeleme',
            ],
        ],
        'abrasion' => 'Sürtünme / aşınma',
        'abrasions' => [
            'low' => [
                'title' => 'Az',
                'text' => 'pürüzsüz yüzey',
            ],
            'mid' => [
                'title' => 'Orta',
                'text' => 'standart malzeme',
            ],
            'high' => [
                'title' => 'Çok',
                'text' => 'kum, metal, aşındırıcı',
            ],
        ],
    ],
    'environment' => [
        'title' => 'Çalışma ortamı nasıl?',
        'temperature' => 'Çalışma sıcaklığı',
        'options' => [
            'oil' => [
                'title' => 'Yağ / gres',
                'text' => 'Yağ, benzin, motorin teması',
                'short' => 'yağ',
            ],
            'water' => [
                'title' => 'Su / buhar',
                'text' => 'Yıkama, nem, buhar',
                'short' => 'su',
            ],
            'food' => [
                'title' => 'Gıda',
                'text' => 'Ürünle doğrudan temas',
                'short' => 'gıda',
            ],
        ],
    ],
    'result' => [
        'label' => 'Öneri',
        'material' => 'Malzeme',
        'hardness' => 'Sertlik',
        'why' => 'Neden bu?',
        'products' => 'Uygun ürünler',
        'disclaimer' => 'Bu genel bir öneridir. Nihai malzeme ve sertliği fabrika mühendisi koşullarınızı inceleyerek onaylar.',
        'request' => 'Bu öneriyle talep gönder →',
        'restart' => 'Baştan başla',
        'form_title' => 'Telefonunuzu bırakın — mühendis seçimi kontrol edip fiyat versin',
    ],
    'notes' => [
        'pu' => 'Aşınma ve yüke en dayanıklı',
        'sil' => 'Yüksek sıcaklığa dayanıklı',
        'epdm' => 'Sıcak su ve buhara dayanıklı',
        'nbr' => 'Yağ ve greslere dayanıklı',
    ],
    'reasons' => [
        'hot' => 'Sıcaklık :temp °C — poliüretan ve normal kauçuk için çok yüksek.',
        'steam' => 'Sıcak su veya buhar var — EPDM bu ortamda kararlı çalışır.',
        'oil_light' => 'Yağ var, yük ve aşınma düşük — NBR yeterli ve ekonomik.',
        'pu' => 'Poliüretan aşınma ve yüke kauçuktan çok daha dayanıklıdır.',
        'pu_oil' => 'Poliüretan yağ ve greslere de iyi dayanır.',
        'food' => 'Gıda teması — :food_material seçilir.',
        'cold' => 'Düşük sıcaklık (:temp °C) — dona dayanıklı bir kalite gerekir.',
        'load_high' => 'Yüksek yük — daha sert malzeme şeklini korur.',
        'load_low' => 'Hafif yük — daha yumuşak malzeme daha iyi tutar ve pürüzsüz çalışır.',
        'load_mid' => 'Orta yük — genel amaçlı sertlik.',
    ],
    'aside' => [
        'live' => 'Canlı öneri',
        'answers' => 'Yanıtlarınız',
        'part' => 'Parça',
        'industry' => 'Sektör',
        'load' => 'Yük',
        'environment' => 'Ortam',
        'abrasion' => 'aşınma',
    ],
    'back' => '← Geri',
    'next' => 'Devam',
    'show' => 'Sonucu gör',
    'aside_label' => 'Seçiminiz',
];
