<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Управление запуском периодических задач
    |--------------------------------------------------------------------------
    */

    // Доступность обновления сертификатов
    'certificates' => (bool) env('JOB_CERTIFICATES', false),

    // Уведомление об изменении данных сотрудников
    'staff_notifications' => (bool) env('JOB_STAFF_NOTIFICATIONS', false),

    // Доступность задач синхронизации с Yclients
    'yclients' => [
        'comments'             => (bool) env('JOB_YCLIENTS_COMMENTS', false),
        'company_month_stat'   => (bool) env('JOB_YCLIENTS_COMPANY_MONTH_STAT', false),
        'company_staff'        => (bool) env('JOB_YCLIENTS_COMPANY_STAFF', false),
        'records'              => (bool) env('JOB_YCLIENTS_RECORDS', false),
        'staff_month_stats'    => (bool) env('JOB_YCLIENTS_STAFF_MONTH_STATS', false),
        'staff_work_days'      => (bool) env('JOB_YCLIENTS_STAFF_WORK_DAYS', false),
        'storage_transactions' => (bool) env('JOB_YCLIENTS_STORAGE_TRANSACTIONS', false),
        'transactions'         => (bool) env('JOB_YCLIENTS_TRANSACTIONS', false),

        /** Deprecated */
        'company_daily_stat' => (bool) env('JOB_YCLIENTS_COMPANY_DAILY_STAT', false),
        'staff_daily_stats'  => (bool) env('JOB_YCLIENTS_STAFF_DAILY_STAT', false),
    ],

    // Доступность задач синхронизации с Mango
    'mango' => [
        'blacklist'    => (bool) env('JOB_MANGO_BLACKLIST', false),
        'daily_calls'  => (bool) env('JOB_MANGO_DAILY_CALLS', false),
        'recent_calls' => (bool) env('JOB_MANGO_RECENT_CALLS', false),
    ],

    // Отчеты партнеров по аналитике
    'partner' => [
        'daily_missed_calls' => (bool) env('JOB_PARTNER_DAILY_MISSED_CALLS', false),
        'lost_clients'       => (bool) env('JOB_PARTNER_LOST_CLIENTS', false),
        'new_clients'        => (bool) env('JOB_PARTNER_NEW_CLIENTS', false),
        'returned_clients'   => (bool) env('JOB_PARTNER_RETURNED_CLIENTS', false),

        'video_reminders'         => (bool) env('JOB_PARTNER_VIDEO_REMINDERS', false),
        'wahelp_report_reminders' => (bool) env('JOB_PARTNER_WAHELP_REPORT_REMINDERS', false),
    ],
];
