<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*Route::prefix('debug')->group(function () {
    Route::get('/certificates/update',
        [App\Http\Controllers\Sheet\CertificateController::class, 'update']
    );
    Route::get('/certificates/duplicates',
        [App\Http\Controllers\Sheet\CertificateController::class, 'duplicateRows']
    );
});*/
