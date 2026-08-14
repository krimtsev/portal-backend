<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Управление запуском периодических задач
    |--------------------------------------------------------------------------
    */

    // Доступность обновления сертификатов
    'certificates' => (bool) env('JOB_CERTIFICATES', true),

    // Доступность задач синхронизации с Yclients
    'yclients' => (bool) env('JOB_YCLIENTS', true),

    // Доступность задач синхронизации с Mango
    'mango' => (bool) env('JOB_MANGO', true),

    // Уведомление об изменении данных сотрудников
    'staff_notifications' => (bool) env('JOB_STAFF_NOTIFICATIONS', true),

    // Отчеты партнеров по аналитике
    'partner_reports' => (bool) env('JOB_PARTNER_REPORTS', true),
];
