<?php

// Translation of lang/ru/shore.php (same keys).
return [
    'category' => 'Seçim',
    'title' => 'Shore A sertliği nasıl seçilir?',
    'excerpt' => 'Yanlış sertlik, parçanın hızlı aşınmasının en yaygın nedenidir. İşte seçim için pratik bir rehber.',
    'author' => '[Yazar — baş mühendis]',
    'minutes' => 6,
    'cover' => 'Poliüretan merdane sertliğinin durometre ile ölçülmesi',
    'sections' => [
        'what' => 'Shore A nedir?',
        'scale' => 'Ölçeği hissedin',
        'mistake' => '"Daha sert daha iyi" hatası',
        'choice' => 'Koşullara göre seçim',
        'summary' => 'Sonuç',
    ],
    'what' => [
        'Shore A, elastik malzemelerin (kauçuk, poliüretan) sertlik ölçeğidir. Cihaz — durometre — malzemeye bir iğne bastırır ve ne kadar battığını ölçer. Sonuç 0 ile 100 arasındadır: sayı büyüdükçe malzeme sertleşir.',
        'Sertlik, parçanın nasıl çalıştığını belirler: yüzeyi ne kadar "tuttuğunu", yük altında şeklini korumasını, aşınmaya direncini ve titreşimi sönümlemesini.',
    ],
    'scale' => [
        'intro' => 'Rakamlar soyut gelebilir. Kaydırıcıyı hareket ettirin — her sertliği tanıdık nesnelerle karşılaştırın:',
        'label' => 'Etkileşimli ölçek',
        'aria' => 'Shore A değeri',
        'feels' => 'Yaklaşık olarak',
        'used' => 'Kullanıldığı parçalar',
        'note' => 'Karşılaştırmalar yaklaşıktır — her malzeme için kesin değer ölçülür.',
        'points' => [
            [
                30,
                'Lastik bant',
                'Çok yumuşak contalar',
            ],
            [
                45,
                'Silgi',
                'Yumuşak tutucu makaralar',
            ],
            [
                60,
                'Yumuşak ayakkabı tabanı',
                'Boya ve laminasyon merdaneleri',
            ],
            [
                72,
                'Araba lastiği',
                'Tutucu ve çekici merdaneler',
            ],
            [
                82,
                'Ayakkabı topuğu',
                'Konveyör merdaneleri, burçlar',
            ],
            [
                90,
                'Sert poliüretan tekerlek',
                'Tekerlekler, sıyırıcılar',
            ],
            [
                96,
                'Kaykay tekerleği',
                'Pres ve haddeleme merdaneleri, yaylar',
            ],
        ],
    ],
    'mistake' => [
        'intro' => 'Çoğu kişi uzun ömürlü olsun diye en sert malzemeyi ister. Ama çok sert bir merdane:',
        'items' => [
            'yüzeyi kötü tutar ve kayar;',
            'darbeyi sönümlemez — rulmanlar ve göbek daha hızlı aşınır;',
            'matbaada boyayı düzensiz aktarır.',
        ],
        'outro' => 'Tersine, çok yumuşak malzeme yüksek yük altında deforme olur, ısınır ve çatlamaya başlar.',
    ],
    'choice' => [
        'intro' => 'Genel yönelim şöyledir (nihai değeri mühendis belirler):',
        'items' => [
            [
                '60–75 A',
                '— tutma, boya aktarımı, laminasyon, hassas yüzeyler;',
            ],
            [
                '80–90 A',
                '— konveyör ve çekme merdaneleri, genel amaçlı işler;',
            ],
            [
                '92–95 A',
                '— pres, haddeleme, yüksek yük ve aşındırıcı ortam.',
            ],
        ],
        'outro' => 'Sıcaklık, yağ ve su da önemlidir: malzeme seçimini etkilerler, sertlik ise o malzeme içinde seçilir.',
    ],
    'summary' => 'Doğru sertlik; tutuş, dayanıklılık ve yük arasında bir dengedir. Emin değilseniz çalışma koşullarını yazın — önerelim.',
];
