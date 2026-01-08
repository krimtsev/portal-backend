<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware(['auth:sanctum', 'role:admin,sysadmin'])
    ->group(function () {

        Route::get('partners/short-list', [Controllers\Partners\PartnerController::class, 'shortList']);

        Route::get('ticket-categories/list', [Controllers\Tickets\TicketsCategoriesController::class, 'list']);
        Route::post('tickets/list', [Controllers\Tickets\TicketsController::class, 'list']);

        Route::get('ticket/{id}', [Controllers\Tickets\TicketsController::class, 'get']);
        Route::post('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'update']);
        Route::get('ticket/{ticket}/download/{name}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
});
