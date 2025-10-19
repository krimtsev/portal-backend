<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
Route::prefix('debug')->group(function () {
    Route::get('/certificates/update',
        [App\Http\Tasks\Sheet\UpdateCertificatesTask::class, 'update']
    );
    Route::get('/certificates/duplicates',
        [App\Http\Tasks\Sheet\UpdateCertificatesTask::class, 'duplicateRows']
    );
});
*/
