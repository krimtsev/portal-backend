<?php

// Обновление сертификатов из Google Sheets
Schedule::command('certificates:sync')->dailyAt('02:00');

// Синхронизация основных показателей компании из YClients (Royalty)
Schedule::command('yclients:sync-company-stats')->dailyAt('03:00');

// Синхронизация списка записей компании из YClients
Schedule::command('yclients:sync-records')->dailyAt('03:30');

// Синхронизация комментариев из YClients
Schedule::command('php artisan yclients:sync-comments')->dailyAt('04:00');

// Синхронизация товарных транзакций компании из YClients
Schedule::command('php artisan yclients:sync-storage-transactions')->dailyAt('04:30');

// Синхронизация статистики по сотрудникам из YClients
Schedule::command('yclients:sync-staff-stats')->dailyAt('05:00');

// Синхронизация транзакций по сотрудникам из YClients
Schedule::command('yclients:sync-staff-transactions')->dailyAt('05:30');

// Синхронизация транзакций компании из YClients
Schedule::command('php artisan yclients:sync-transactions')->dailyAt('06:00');

// Удаляем задачи которые старшее 30 дней
Schedule::command('queue:prune-batches --hours=720')->dailyAt('07:00');
