<?php

use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsFilters;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsResponse;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsClient;
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
