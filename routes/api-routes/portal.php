<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user-data', [AuthController::class, 'userData']);

        Route::get('/certificates', [Portal\Sheet\CertificateController::class, 'list']);

        Route::prefix('contacts')->group(function () {
            Route::get('/franchisee', [Portal\Contacts\FranchiseeController::class, '__invoke']);
        });
    });
});
