<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [Controllers\Auth\AuthController::class, 'login']);
    Route::post('logout', [Controllers\Auth\AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
        Route::get('home', [Controllers\App\AppController::class, 'homeData']);

        Route::get('user-data', [Controllers\Auth\AuthController::class, 'userData']);

        Route::post('certificates', [Controllers\Sheet\CertificateController::class, 'list']);

        Route::prefix('contacts')->group(function () {
            Route::post('franchisee', [Controllers\Contacts\FranchiseeController::class, 'list']);
        });

        Route::prefix('cloud')->group(function () {
            Route::get('list', [Controllers\Cloud\CloudController::class, 'list']);

            Route::get('folder/{folder}/download/{fileName}', [Controllers\Cloud\CloudFilesController::class, 'download']);
        });

        Route::get('user-partners', [Controllers\Partners\PartnerController::class, 'getUserPartners']);

        Route::prefix('files')->group(function () {
            Route::get('download/{category}/{fileName}', [Controllers\Files\FileController::class, 'download']);
            Route::get('render/{category}/{fileName}', [Controllers\Files\FileController::class, 'render']);
        });
    });
});

Route::get('media/{category}/{fileName}', [Controllers\Files\PublicFileController::class, 'show'])
    ->where([
        'category' => '[a-zA-Z0-9_\-]+',
        'fileName' => '[a-zA-Z0-9_\-\.]+',
    ]);
