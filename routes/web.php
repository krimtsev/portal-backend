<?php

use App\Integrations\Yclients\DTO\Analytics\CompanyStatsDto;
use App\Integrations\Yclients\YclientsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return 'Hello World'; });

Route::get('/test/{id}', function (string $id, YclientsApi $yclients, Request $request) {
    $rawData = $yclients->analytics()->getCompanyStats(
        companyId: $id,
        dateFrom: $request->query('date_from'),
        dateTo: $request->query('date_to'),
        staffId: $request->query('staff_id')
    );

    $dto = CompanyStatsDto::fromArray($rawData);

    return response()->json($dto->toArray());
});
