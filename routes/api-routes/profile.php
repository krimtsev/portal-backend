<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/profile')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::prefix('user-profile')->group(function () {
            Route::get('/', [Controllers\User\UserProfileController::class, 'show']);
            Route::put('/', [Controllers\User\UserProfileController::class, 'update']);
        });

        Route::put('/change-password', [Controllers\Auth\ChangePasswordController::class, 'update']);

        Route::prefix('ticket-categories')->group(function () {
            Route::get('/list', [Controllers\Tickets\TicketsCategoriesController::class, 'list']);
            Route::get('/slug/{category:slug}', [Controllers\Tickets\TicketsCategoriesController::class, 'getCategoryBySlug']);
        });

        Route::prefix('tickets')->group(function () {
            Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
            Route::post('ticket', [Controllers\Tickets\TicketsController::class, 'create']);
            Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
            Route::post('ticket/{ticket}/message', [Controllers\Tickets\TicketsController::class, 'updateMessage']);
            Route::delete('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'remove']);
            Route::get('ticket/{ticket}/download/{name}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
        });
    });
