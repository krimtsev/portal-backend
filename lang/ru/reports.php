<?php

return [
    'clients' => [
        'title' => 'Отчет',
        'branch' => 'Филиал',
        'date' => 'Дата',
        'period' => 'за :count',
        'empty' => 'Для данного типа событий клиентов нет.',
        'days' => ':count сутки|:count суток|:count суток',
        'table' => [
            'sheet_title' => 'Отчет по клиентам',
            'no_name' => 'Без имени',
            'no_data' => '—',
            'link_tooltip' => 'Открыть карточку в YClients',
            'header' => [
                'name' => 'Имя',
                'phone' => 'Телефон',
                'services' => 'Сервисы',
                'other_branch_services' => 'Сервисы в другом филиале',
                'date' => 'Дата посещения',
                'partner' => 'Филиал',
                'link' => 'Ссылка на YClients',
            ],
        ],
    ],
    'company_staff' => [
        'not_specified' => 'Не указано',
        'specialization_not_specified' => 'Не указана',
        'phone_not_specified' => 'Не указан',

        'created_header' => 'Добавлен новый сотрудник.',
        'updated_header' => 'Изменены данные сотрудника.',

        'branch' => 'Филиал: :branch',
        'name' => 'Имя: :name',
        'specialization' => 'Специализация: :specialization',
        'phone' => 'Телефон: :phone',

        'fields' => [
            'name' => 'Имя',
            'specialization' => 'Специализация',
            'phone' => 'Телефон',
            'status' => 'Статус',
        ],

        'status' => [
            'fired' => 'Уволен',
            'working' => 'Работает',
        ],
    ]
];
