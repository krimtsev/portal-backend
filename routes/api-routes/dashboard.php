<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('partners/short-list', [Controllers\Partners\PartnerController::class, 'shortList']);

        Route::get('ticket-categories/list', [Controllers\Tickets\TicketsCategoriesController::class, 'list']);
        Route::post('tickets/list', [Controllers\Tickets\TicketsController::class, 'list']);
});
