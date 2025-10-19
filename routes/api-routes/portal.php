<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user-data', [AuthController::class, 'userData']);

        Route::get('/certificates', [Portal\Sheet\CertificateController::class, 'list']);

        Route::prefix('contacts')->group(function () {
            Route::get('/franchisee', [Portal\Contacts\FranchiseeController::class, 'list']);
        });

        Route::prefix('cloud')->group(function () {
            Route::get('/list', [Portal\Cloud\CloudController::class, 'list']);

            Route::get('/download', [Portal\Cloud\CloudDownloadController::class, 'download']);
        });
    });
});
