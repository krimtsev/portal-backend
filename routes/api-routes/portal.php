<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [Controllers\Auth\AuthController::class, 'login']);
    Route::post('logout', [Controllers\Auth\AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('user-data', [Controllers\Auth\AuthController::class, 'userData']);

        Route::post('certificates', [Controllers\Sheet\CertificateController::class, 'list']);

        Route::prefix('contacts')->group(function () {
            Route::post('franchisee', [Controllers\Contacts\FranchiseeController::class, 'list']);
        });

        Route::prefix('cloud')->group(function () {
            Route::get('list', [Controllers\Cloud\CloudController::class, 'list']);

            Route::get('download', [Controllers\Cloud\CloudFilesController::class, 'download']);
        });

        Route::get('messages', [Controllers\Message\MessageController::class, 'list']);

        Route::get('user-partners', [Controllers\Partners\PartnerController::class, 'getUserPartners']);
    });
});
