<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware([
        'auth:sanctum',
        'dashboard.context'
    ])
    ->group(function () {
        Route::prefix('partners')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::get('short-list', [Controllers\Partners\PartnerController::class, 'shortList']);
            });

        Route::prefix('ticket-categories')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::get('list', [Controllers\Tickets\TicketsCategoriesController::class, 'list']);
            });

        Route::prefix('tickets')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
                Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
                Route::post('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'update']);
                Route::get('ticket/{ticket}/download/{name}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
            });

        Route::prefix('users')
            ->middleware(['role:sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Users\UserController::class, 'list']);
            });
});
