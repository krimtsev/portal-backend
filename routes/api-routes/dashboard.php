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
                Route::get('options', [Controllers\Partners\PartnerController::class, 'options']);
            });

        Route::prefix('partners')
            ->middleware(['role:sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Partners\PartnerController::class, 'list']);
                Route::get('partner/{partner}', [Controllers\Partners\PartnerController::class, 'get']);
                Route::post('partner/{partner}', [Controllers\Partners\PartnerController::class, 'create']);
                Route::put('partner/{partner}', [Controllers\Partners\PartnerController::class, 'update']);
            });

        Route::prefix('partner-groups')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::get('options', [Controllers\Partners\PartnerGroupController::class, 'options']);
            });

        Route::prefix('partner-groups')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Partners\PartnerGroupController::class, 'list']);
                Route::get('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'get']);
                Route::post('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'create']);
                Route::put('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'update']);
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
                Route::get('user/{user}', [Controllers\Users\UserController::class, 'get']);
                Route::post('user/{user}', [Controllers\Users\UserController::class, 'create']);
                Route::put('user/{user}', [Controllers\Users\UserController::class, 'update']);
            });
});
