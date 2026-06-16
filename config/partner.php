<?php

$partner = env('PARTNER_NAME', 'default');

$path = base_path("tenants/{$partner}");

return [
    'current' => $partner,

    'secrets' => [
        'google' => [
            'credentials' => ".secrets/google/credentials.json",
            'certificate' => ".secrets/google/certificate.json",
        ],
    ],
];
