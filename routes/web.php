<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // 1. Берем первый тикет и обязательно подгружаем автора (user)
    $ticket = \App\Models\Ticket\Ticket::with('user')->first();

    if (!$ticket) {
        return "В базе данных пока нет ни одного тикета.";
    }

    // 2. Создаем экземпляр уведомления
    $notification = new \App\Notifications\Ticket\TicketCreatedNotification($ticket);

    // 3. Рендерим письмо для первого попавшегося пользователя
    $user = \App\Models\User\User::first();

    return $notification->toMail($user)->render();
});

/**
Route::prefix('debug')->group(function () {
    Route::get('/certificates/update',
        [App\Http\Tasks\Sheet\UpdateCertificatesTask::class, 'update']
    );
    Route::get('/certificates/duplicates',
        [App\Http\Tasks\Sheet\UpdateCertificatesTask::class, 'duplicateRows']
    );
});
*/
