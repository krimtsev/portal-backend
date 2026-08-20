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
    ->timezone('Europe/Moscow')
    ->dailyAt('01:00');

/**
 * Синхронизация пропущенных звонков из Mango API
 * Обновляем данные за прошедший день.
 */
Schedule::command('mango:sync-daily-calls')
    ->timezone('Europe/Moscow')
    ->dailyAt('01:15');

/**
 * Синхронизация сотрудников из YClients
 * Должен быть всегда раньше запросов в выборке по сотрудникам
 */
Schedule::command('yclients:sync-staff-work-days')
    ->timezone('Europe/Moscow')
    ->dailyAt('01:30');

/**
 * Синхронизация списка записей компании из YClients
 */
Schedule::command('yclients:sync-records')
    ->timezone('Europe/Moscow')
    ->dailyAt('03:00');

/**
 * Синхронизация комментариев из YClients
 */
Schedule::command('yclients:sync-comments')
    ->timezone('Europe/Moscow')
    ->dailyAt('03:30');

/**
 * Синхронизация товарных транзакций компании из YClients
 */
Schedule::command('yclients:sync-storage-transactions')
    ->timezone('Europe/Moscow')
    ->dailyAt('04:00');

/**
 * Синхронизация транзакций компании из YClients
 */
Schedule::command('yclients:sync-transactions')
    ->timezone('Europe/Moscow')
    ->dailyAt('04:30');

/**
 * Fixme: с 5:00 - 7:00 MSK
 * Задачи не ставим, на ISP проводятся технические работы
 */

/**
 * Синхронизация основных показателей компании с выбором за месяц из YClients (Royalty)
 */
Schedule::command('yclients:sync-company-month-stats')
    ->timezone('Europe/Moscow')
    ->monthlyOn(1, '09:00');

/**
 * Синхронизация статистики по сотрудникам с выбором за месяц из YClients
 */
Schedule::command('yclients:sync-staff-month-stats')
    ->timezone('Europe/Moscow')
    ->monthlyOn(1, '09:30');

/**
 * Синхронизация статистики данных сотрудников из YClients
 * Рассылка изменений данных сотрудников
 */
Schedule::command('yclients:sync-company-staff')
    ->timezone('Europe/Moscow')
    ->cron('0 10,14,18,22 * * *');

/**
 * Синхронизация черного списка номеров из Mango
 */
Schedule::command('mango:sync-blacklist')
    ->timezone('Europe/Moscow')
    ->hourly();

/**
 * Рассылка отчетов по новым клиентам
 */
Schedule::command('report:new-clients')
    ->timezone('Europe/Moscow')
    ->dailyAt('10:00');

/**
 * Рассылка отчетов по вернувшимся клиентам
 */
Schedule::command('report:returned-clients')
    ->timezone('Europe/Moscow')
    ->dailyAt('10:15');

/**
 * Рассылка отчетов по потерянным клиентам
 */
Schedule::command('report:lost-clients')
    ->timezone('Europe/Moscow')
    ->dailyAt('10:30');

/**
 * Собираем статистику по звонкам из Манго телефонии
 */
Schedule::command('mango:sync-recent-calls')
    ->everyTwoMinutes();

/**
 * Удаляем задачи, которые зависли старшее 30 дней
 */
Schedule::command('queue:prune-batches --hours=720')
    ->timezone('Europe/Moscow')
    ->dailyAt('11:00');

/**
 * Уведомление партнеров про видео отчеты
 */
Schedule::command('reports:send-video-reminders')
    ->timezone('Europe/Moscow')
    ->dailyAt('11:00');

/**
 * Уведомление партнеров про WhatsApp
 */
Schedule::command('reports:send-wahelp-reminders')
    ->timezone('Europe/Moscow')
    ->dailyAt('12:00');

/**
 * Рассылка ежедневных отчетов о пропущенных звонках
 */
Schedule::command('report:send-daily-missed-calls')
    ->timezone('Europe/Moscow')
    ->dailyAt('22:00');
