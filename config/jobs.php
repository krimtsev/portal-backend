<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Управление запуском периодических задач
    |--------------------------------------------------------------------------
    */

    // Доступность обновления сертификатов
    'certificates' => (bool) env('JOB_CERTIFICATES', false),

    // Доступность задач синхронизации с Yclients
    'yclients' => (bool) env('JOB_YCLIENTS', false),

    // Доступность задач синхронизации с Mango
    'mango' => (bool) env('JOB_MANGO', false),

    // Уведомление об изменении данных сотрудников
    'staff_notifications' => (bool) env('JOB_STAFF_NOTIFICATIONS', false),

    // Отчеты партнеров по аналитике
    'partner_reports' => (bool) env('JOB_PARTNER_REPORTS', false),
];
