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

        Route::get('/ticket-categories', [Controllers\Tickets\TicketsCategoriesController::class, 'all']);
        Route::get('/ticket-category/{category:slug}', [Controllers\Tickets\TicketsCategoriesController::class, 'getCategoryBySlug']);

        Route::post('/tickets', [Controllers\Tickets\TicketsController::class, 'list']);

        Route::prefix('ticket')->group(function () {
            Route::post('/', [Controllers\Tickets\TicketsController::class, 'create']);
            Route::get('{id}', [Controllers\Tickets\TicketsController::class, 'get']);
            Route::post('{ticket}/message', [Controllers\Tickets\TicketsController::class, 'updateMessage']);
            Route::put('{ticket}', [Controllers\Tickets\TicketsController::class, 'update']);
            Route::delete('{ticket}', [Controllers\Tickets\TicketsController::class, 'remove']);
            Route::get('{ticket}/download/{name}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
        });
    });
