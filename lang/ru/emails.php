<?php

return [
    'ticket' => [
        'subject' => [
            'created' => 'Новая заявка #:id',
            'all_changed' => 'Обновление заявки #:id:',
            'status_changed' => 'Изменен статус заявки #:id',
            'text_changed' => 'Новое сообщение в заявке #:id',
        ],
        'description' => [
            'greeting' => 'Здравствуйте, :name.',
            'created_body' => 'Создана новая заявка #:id',
            'updated_body' => 'Обновление в заявке #:id',
            'subject_title' => 'Тема: ":title"',
            'status_changed_to' => 'Текущий статус заявки изменен на: :status',
            'comment_added' => 'Пользователь :name добавил(а) сообщение:',
            'footer_text' => 'Вы получили это письмо, так как являетесь сотрудником ответственного отдела.',
        ],
    ],
    'user' => [
        'subject' => [
            'password_changed' => 'Ваш пароль был изменен',
        ],
        'description' => [
            'password_changed_title' => 'Безопасность учетной записи',
            'greeting' => 'Здравствуйте, :name.',
            'password_changed_body' => 'Уведомляем вас о том, что пароль от вашей учетной записи был успешно изменен.',
            'warning_text' => 'Если вы не совершали этого действия, пожалуйста, немедленно свяжитесь с системным администратором.',
        ],
    ],
];
