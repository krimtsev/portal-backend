<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/profile')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::prefix('user-profile')->group(function () {
            Route::get('/', [Controllers\Users\UserProfileController::class, 'show']);
        });

        Route::put('/change-password', [Controllers\Auth\ChangePasswordController::class, 'update']);

        Route::prefix('tickets')->group(function () {
            Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
            Route::post('ticket', [Controllers\Tickets\TicketsController::class, 'create']);
            Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
            Route::post('ticket/{ticket}/message', [Controllers\Tickets\TicketsController::class, 'updateMessage']);
            Route::delete('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'remove']);
            Route::get('ticket/{ticket}/download/{fileName}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
        });

        Route::prefix('statistics')
            ->group(function () {
                Route::get('staff/compare', [Controllers\Statistics\StatisticsStaffController::class, 'compare']);
                Route::get('staff/details', [Controllers\Statistics\StatisticsStaffController::class, 'staffDetails']);
                Route::get('staff/total-compare', [Controllers\Statistics\StatisticsStaffController::class, 'totalCompare']);
            });
    });
