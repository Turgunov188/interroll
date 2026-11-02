<?php

// Extra answers sent with a form (details[key] = value), shown to the manager in the lead (Russian only).
return [
    'details' => [
        'topic' => [
            'label' => 'Тема',
            'values' => ['price' => 'Запрос цены', 'recoating' => 'Восстановление покрытия', 'technical' => 'Технический вопрос', 'partnership' => 'Сотрудничество'],
        ],
        'reply_via' => [
            'label' => 'Как ответить',
            'values' => ['phone' => 'Звонок', 'telegram' => 'Telegram', 'email' => 'Email'],
        ],
        'part' => ['label' => 'Деталь'],
        'industry' => ['label' => 'Отрасль'],
        'conditions' => ['label' => 'Условия работы'],
        'environment' => ['label' => 'Среда'],
        'recommendation' => ['label' => 'Рекомендация мастера подбора'],
    ],
];
