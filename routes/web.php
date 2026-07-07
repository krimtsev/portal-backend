<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return 'Hello World'; });

Route::prefix('debug')->group(function () {
    Route::get('/timezone', function () {
        return response()->json([
            'config' => [
                'config_app_timezone' => config('app.timezone'),
            ],
            'php_and_carbon' => [
                'carbon_now'           => Carbon::now()->toIso8601String(),
                'php_date_now'         => date('Y-m-d H:i:s P'),
                'php_default_timezone' => date_default_timezone_get(),
            ],
        ]);
    });
});
