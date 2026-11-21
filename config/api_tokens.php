<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Токены внешних порталов (Bearer)
    |--------------------------------------------------------------------------
    */
    'tokens' => array_filter(
        [
            env('PUBLIC_PARTNER_TOKEN') => [
                'portal'    => 'partner',
                'abilities' => ['partner.read'],
            ],
        ],
        static fn ($key) => is_string($key) && $key !== '',
        ARRAY_FILTER_USE_KEY,
    ),
];
