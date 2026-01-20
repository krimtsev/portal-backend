<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware([
        'auth:sanctum',
        'role:admin,sysadmin',
        'dashboard.context'
    ])
    ->group(function () {
        Route::prefix('partners')->group(function () {
            Route::get('short-list', [Controllers\Partners\PartnerController::class, 'shortList']);
        });

        Route::prefix('ticket-categories')->group(function () {
            Route::get('list', [Controllers\Tickets\TicketsCategoriesController::class, 'list']);
        });

        Route::prefix('tickets')->group(function () {
            Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
            Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
            Route::post('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'update']);
            Route::get('ticket/{ticket}/download/{name}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
        });
});
