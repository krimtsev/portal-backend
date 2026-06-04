<?php

return [
    'ticket' => [
        'subject' => [
            'created' => 'New Ticket #:id',
            'all_changed' => 'Ticket Update #:id',
            'status_changed' => 'Ticket Status Changed #:id',
            'text_changed' => 'New Message in Ticket #:id',
        ],
        'description' => [
            'greeting' => 'Hello, :name.',
            'created_body' => 'A new ticket #:id has been created',
            'updated_body' => 'Update in ticket #:id',
            'subject_title' => 'Subject: ":title"',
            'status_changed_to' => 'Current ticket status changed to: :status',
            'comment_added' => 'User :name added a message:',
            'footer_text' => 'You received this email because you are an employee of the responsible department.',
        ],
    ],
    'user' => [
        'subject' => [
            'password_changed' => 'Your password has been changed',
        ],
        'description' => [
            'password_changed_title' => 'Account Security',
            'greeting' => 'Hello, :name.',
            'password_changed_body' => 'This is to notify you that your account password has been successfully changed.',
            'warning_text' => 'If you did not perform this action, please contact your system administrator immediately.',
        ],
    ],
];
