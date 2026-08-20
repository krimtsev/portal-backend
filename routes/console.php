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
 * Синхронизация пропущенных звонков из Mango API
 * Обновляем данные за прошедший день.
 */
Schedule::command('mango:sync-calls --date=yesterday --silent --protected')
    ->timezone('Europe/Moscow')
    ->dailyAt('01:15');

/**
 * Синхронизация сотрудников из YClients
 * Должен быть всегда раньше запросов в выборке по сотрудникам
 */
Schedule::command('yclients:sync-staff-work-days')
    ->dailyAt('01:30')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация списка записей компании из YClients
 */
Schedule::command('yclients:sync-records')
    ->dailyAt('03:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация комментариев из YClients
 */
Schedule::command('yclients:sync-comments')
    ->dailyAt('03:30')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация товарных транзакций компании из YClients
 */
Schedule::command('yclients:sync-storage-transactions')
    ->dailyAt('04:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация транзакций компании из YClients
 */
Schedule::command('yclients:sync-transactions')
    ->dailyAt('04:30')
    ->timezone('Europe/Moscow');

/**
 * Fixme: с 5:00 - 7:00 MSK
 * Задачи не ставим, на ISP проводятся технические работы
 */

/**
 * Синхронизация основных показателей компании с выбором за месяц из YClients (Royalty)
 */
Schedule::command('yclients:sync-company-month-stats')
    ->monthlyOn(1, '09:00')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация статистики по сотрудникам с выбором за месяц из YClients
 */
Schedule::command('yclients:sync-staff-month-stats')
    ->monthlyOn(1, '09:30')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация статистики данных сотрудников из YClients
 * Рассылка изменений данных сотрудников
 */
Schedule::command('yclients:sync-company-staff')
    ->cron('0 10,14,18,22 * * *')
    ->timezone('Europe/Moscow');

/**
 * Синхронизация черного списка номеров из Mango
 */
Schedule::command('mango:sync-blacklist')
    ->hourly()
    ->timezone('Europe/Moscow');

/**
 * Рассылка отчетов по новым клиентам
 */
Schedule::command('report:new-clients')
    ->dailyAt('10:00')
    ->timezone('Europe/Moscow');

/**
 * Рассылка отчетов по вернувшимся клиентам
 */
Schedule::command('report:returned-clients')
    ->dailyAt('10:15')
    ->timezone('Europe/Moscow');

/**
 * Рассылка отчетов по потерянным клиентам
 */
Schedule::command('report:lost-clients')
    ->dailyAt('10:30')
    ->timezone('Europe/Moscow');

/**
 * Собираем статистику по звонкам из Манго телефонии
 */
Schedule::command('mango:sync-calls')
    ->everyTwoMinutes();

/**
 * Удаляем задачи, которые зависли старшее 30 дней
 */
Schedule::command('queue:prune-batches --hours=720')
    ->dailyAt('11:00')
    ->timezone('Europe/Moscow');

/**
 * Уведомление партнеров про видео отчеты
 */
Schedule::command('reports:send-video-reminders')
    ->dailyAt('11:00')
    ->timezone('Europe/Moscow');

/**
 * Уведомление партнеров про WhatsApp
 */
Schedule::command('reports:send-wahelp-reminders')
    ->dailyAt('12:00')
    ->timezone('Europe/Moscow');

/**
 * Рассылка ежедневных отчетов о пропущенных звонках
 */
Schedule::command('report:send-daily-missed-calls')
    ->dailyAt('22:00')
    ->timezone('Europe/Moscow');
