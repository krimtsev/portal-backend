<?php

use App\Integrations\Telegram\TelegramManager;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
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


    Route::get('/telegram/msg', function (TelegramManager $telegram) {
        $response = $telegram->sendMessage([
            'chat_id' => '-1001993054003',
            'text'    => 'hello'
        ]);

        return response()->json($response);
    });

    Route::get('/telegram/docs', function (TelegramManager $telegram) {
        $filePath = Storage::disk('public')->path('test.xlsx');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $response = $telegram->sendDocument([
            'chat_id'  => '-1001993054003',
            'document' => $filePath,
            'caption'  => 'file',
        ]);


        return response()->json($response);
    });
});
