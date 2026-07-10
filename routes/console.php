<?php

/**
 * Ориентировочный подсчет времени запросов
 * 200 компаний,
 * 1-3 сек на один запрос.
 * 10 сотрудников в филиале
 *
 * по компании 3m / 6m / 10m
 * по сотрудникам 33m / 1h 10m / 1h 40m
 */

/**
 * Обновление сертификатов из Google Sheets
 */
Schedule::command('certificates:sync')
    ->dailyAt('01:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация сотрудников из YClients
 * Должен быть всегда раньше запросов в выборке по сотрудникам
 */
Schedule::command('yclients:sync-staff-work-days')
    ->dailyAt('01:30')
    ->timezone('Europe/Moscow');

// Синхронизация списка записей компании из YClients
Schedule::command('yclients:sync-records')
    ->dailyAt('03:00')
    ->timezone('Europe/Moscow');

// Синхронизация комментариев из YClients
Schedule::command('php artisan yclients:sync-comments')
    ->dailyAt('03:30')
    ->timezone('Europe/Moscow');

// Синхронизация товарных транзакций компании из YClients
Schedule::command('php artisan yclients:sync-storage-transactions')
    ->dailyAt('04:00')
    ->timezone('Europe/Moscow');

// Синхронизация транзакций компании из YClients
Schedule::command('php artisan yclients:sync-transactions')
    ->dailyAt('04:30')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация основных показателей компании с выбором за месяц из YClients (Royalty)
 */
Schedule::command('yclients:sync-company-month-stats')
    ->monthlyOn(1, '05:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация статистики по сотрудникам с выбором за месяц из YClients
 */
Schedule::command('yclients:sync-staff-month-stats')
    ->monthlyOn(1, '06:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация статистики по сотрудникам из YClients
 */
// Schedule::command('yclients:sync-staff-daily-stats')
// ->dailyAt('06:00')
// ->timezone('Europe/Moscow');

/**
 * Синхронизация основных показателей компании за сутки из YClients (Royalty)
 */
// Schedule::command('yclients:sync-company-daily-stats')
// ->dailyAt('05:00')
// ->timezone('Europe/Moscow');

/**
 * Синхронизация статистики данных сотрудников из YClients
 * временно, до добавления наблюдения изменений.
 */
Schedule::command('yclients:sync-company-staff')
    ->monthlyOn(1, '08:00')
    ->timezone('Europe/Moscow');


// Удаляем задачи которые старшее 30 дней
Schedule::command('queue:prune-batches --hours=720')
    ->dailyAt('09:00')
    ->timezone('Europe/Moscow');
