<?php

return [
    'clients' => [
        'title' => 'Report',
        'branch' => 'Branch',
        'date' => 'Date',
        'period' => 'for :count',
        'empty' => 'There are no clients for this event type.',
        'days' => ':count day|:count days',
        'table' => [
            'sheet_title' => 'Client Report',
            'no_name' => 'No name',
            'no_data' => '—',
            'link_tooltip' => 'Open client card',
            'header' => [
                'name' => 'Name',
                'phone' => 'Phone',
                'services' => 'Services',
                'other_branch_services' => 'Other branch services',
                'date' => 'Visit date',
                'partner' => 'Branch',
                'link' => 'Client card link',
            ],
        ],
    ],
    'company_staff' => [
        'not_specified' => 'Not specified',
        'specialization_not_specified' => 'Not specified',
        'phone_not_specified' => 'Not specified',

        'created_header' => 'New employee added.',
        'updated_header' => 'Employee data updated.',

        'branch' => 'Branch: :branch',
        'name' => 'Name: :name',
        'specialization' => 'Specialization: :specialization',
        'phone' => 'Phone: :phone',

        'fields' => [
            'name' => 'Name',
            'specialization' => 'Specialization',
            'phone' => 'Phone',
            'status' => 'Status',
        ],

        'status' => [
            'fired' => 'Fired',
            'working' => 'Active',
        ],
    ],
];
