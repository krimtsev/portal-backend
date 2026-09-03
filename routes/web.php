<?php

use App\Integrations\Mango\MangoApi;
use App\Integrations\Mango\Resources\CallsStats\DTO\CallsStatsRequestFilters;
use App\Integrations\Telegram\TelegramManager;
use App\Models\Yclients\YcCompanyStaff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
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

    /*Route::get('/telegram/msg', function (TelegramManager $telegram) {
        $response = $telegram->sendMessage([
            'chat_id' => '-1001993054003',
            'text'    => 'hello',
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

    Route::get('/telegram/send-photo', function () {
        $token = '6987304578:AAHh45TiX5gFWXPhlD7SqMW_RHKPkY7qGZU';
        $chatId = '-1001993054003';

        $staffList = YcCompanyStaff::query()
            ->whereNotNull('avatar_big')
            ->where('avatar_big', '!=', '')
            ->where('avatar_big', '!=', 'https://be.cdn.yclients.com/images/no-master.png')
            ->orderByDesc('staff_id')
            ->limit(15)
            ->get();

        $results = [];

        foreach ($staffList as $staff) {
            $caption = "Изменены данные сотрудника:\n\n"
                . "Имя: {$staff->name}\n"
                . 'Специализация: ' . ($staff->specialization ?? 'Не указана') . "\n"
                . 'Телефон: ' . ($staff->phone ?? 'Не указан') . "\n"
                . 'Статус: ' . ($staff->fired ? 'Уволен' : 'Работает');

            $response = Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'chat_id' => $chatId,
                'photo'   => $staff->avatar_big,
                'caption' => $caption,
            ]);

            $results[] = [
                'id'       => $staff->id,
                'staff_id' => $staff->staff_id,
                'status'   => $response->status(),
                'response' => $response->json(),
            ];
        }

        return response()->json($results);
    });

    Route::get('mango/bwlists', function (MangoApi $mango) {
        $result = $mango->bwlists()->getBwlists();

        return response()->json($result);
    });

    Route::get('mango/stats/request', function (MangoApi $mango) {
        $filters = new CallsStatsRequestFilters(
            start_date: now()->subDays(7)->format('d.m.Y 00:00:00'),
            end_date: now()->format('d.m.Y H:i:s'),
            limit: 100,
            context_type: 1,
        );

        $result = $mango->callsStats()->statsCallsRequest($filters);

        return response()->json($result);
    });

    Route::get('mango/stats/result/{key}', function (MangoApi $mango, string $key) {
        $result = $mango->callsStats()->statsCallsResult($key);

        return response()->json($result);
    });*/
});
