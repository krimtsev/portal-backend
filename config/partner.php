<?php

$partner = env('PARTNER_NAME', 'default');

return [
    'current' => $partner,

    'secrets' => [
        'google' => [
            'credentials' => base_path('.secrets/google/credentials.json'),
            'certificate' => base_path('.secrets/google/certificate.json'),
        ],
    ],
];
