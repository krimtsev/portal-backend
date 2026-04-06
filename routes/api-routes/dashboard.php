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
                Route::get('export', [Controllers\Partners\PartnerController::class, 'export']);
            });

        Route::prefix('partner-groups')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::get('options', [Controllers\Partners\PartnerGroupController::class, 'options']);
            });

        Route::prefix('partner-groups')
            ->middleware(['role:sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Partners\PartnerGroupController::class, 'list']);
                Route::get('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'get']);
                Route::post('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'create']);
                Route::put('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'update']);
                Route::delete('partner-group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'remove']);
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
                Route::get('ticket/{ticket}/download/{fileName}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
                Route::get('export', [Controllers\Tickets\TicketsController::class, 'export']);

            });

        Route::prefix('users')
            ->middleware(['role:sysadmin'])
            ->group(function () {
                Route::post('list', [Controllers\Users\UserController::class, 'list']);
                Route::get('user/{user}', [Controllers\Users\UserController::class, 'get']);
                Route::post('user/{user}', [Controllers\Users\UserController::class, 'create']);
                Route::put('user/{user}', [Controllers\Users\UserController::class, 'update']);
                Route::get('export', [Controllers\Users\UserController::class, 'export']);
            });

        Route::prefix('cloud')
            ->middleware(['role:admin,sysadmin'])
            ->group(function () {
                Route::post('tree', [Controllers\Cloud\CloudController::class, 'tree']);
                Route::get('options', [Controllers\Cloud\CloudController::class, 'options']);
                Route::get('options-tree', [Controllers\Cloud\CloudController::class, 'optionsTree']);
                Route::get('folder/{folder}', [Controllers\Cloud\CloudController::class, 'get']);
                Route::post('folder/{folder}', [Controllers\Cloud\CloudController::class, 'create']);
                Route::put('folder/{folder}', [Controllers\Cloud\CloudController::class, 'update']);

                Route::get('folder/{folder}/files', [Controllers\Cloud\CloudFilesController::class, 'list']);
                Route::post('folder/{folder}/files', [Controllers\Cloud\CloudFilesController::class, 'upload']);
                Route::put('folder/{folder}/file/{file}', [Controllers\Cloud\CloudFilesController::class, 'update']);
                Route::delete('folder/{folder}/file/{file}', [Controllers\Cloud\CloudFilesController::class, 'remove']);
                Route::get('folder/{folder}/download/{fileName}', [Controllers\Cloud\CloudFilesController::class, 'download']);
            });
});
