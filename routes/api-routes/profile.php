<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/profile')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/user-profile', [Controllers\User\UserProfileController::class, 'show']);
        Route::put('/user-profile', [Controllers\User\UserProfileController::class, 'update']);

        Route::put('/change-password', [Controllers\Auth\ChangePasswordController::class, 'update']);

        Route::get('/ticket-categories', [Controllers\Tickets\TicketsCategoriesController::class, 'all']);
        Route::get('/ticket-category/{category:slug}', [Controllers\Tickets\TicketsCategoriesController::class, 'getCategoryBySlug']);

        Route::post('/tickets', [Controllers\Tickets\TicketsController::class, 'list']);
        Route::post('/ticket', [Controllers\Tickets\TicketsController::class, 'create']);
        Route::get('/ticket/{id}', [Controllers\Tickets\TicketsController::class, 'get']);
        Route::put('/ticket/{id}', [Controllers\Tickets\TicketsController::class, 'update']);
    });
