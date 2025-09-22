<?php

use Illuminate\Support\Facades\Route;

Route::prefix('public/v1')->group(function () {
    Route::middleware(['query.token'])->group(function () {
        // TODO: переделать под Bearer token для внешнего апи
    });
});
