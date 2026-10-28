<?php

namespace App\Enums\DaData;

enum DaDataStatus: string
{
    // действующая
    case ACTIVE = 'ACTIVE';
    // ликвидируется
    case LIQUIDATING = 'LIQUIDATING';
    // ликвидирована
    case LIQUIDATED = 'LIQUIDATED';
    // банкротство
    case BANKRUPT = 'BANKRUPT';
    // в процессе присоединения к другому юрлицу, с последующей ликвидацией
    case REORGANIZING = 'REORGANIZING';
}
