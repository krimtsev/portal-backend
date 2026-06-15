<?php

// Обновление сертификатов из Google Sheets
Schedule::command('certificates:sync')->dailyAt('03:00');

// Синхронизация основных показателей компании из YClients (Royalty)
Schedule::command('yclients:sync-company-stats')->dailyAt('04:00');

// Удаляем задачи которые старшее 30 дней
Schedule::command('queue:prune-batches --hours=720')->dailyAt('07:00');
